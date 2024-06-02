;(function () {

  function show(element) {
    if (typeof element !== 'undefined') {
      element.style.display = ''
    }
  }

  function hide(element) {
    if (typeof element !== 'undefined') {
      element.style.display = 'none'
    }
  }

  async function start(DMF) {
    const prefix = DMF.settings.prefix

    const listeners = [];

    function reset() {
      while (listeners.length > 0) {
        const { element, event, handler } = listeners.pop()
        element.removeEventListener(event, handler)
      }
    }

    function addEventListener(element, event, handler) {
      element.addEventListener(event, handler)
      listeners.push({element, event, handler})
    }

    function initElement(element, plugin) {
      const form = element.closest('form')
      const behaviour = element.dataset[prefix + 'PluginBehaviour'] || 'hide'
      const snippets = DMF.getPluginSnippets(element)

      if (form === null) {
        return
      }

      function handleReset(event) {
        event.preventDefault()
        if (snippets.reset) {
          hide(snippets.reset)
        }
        if (snippets.success) {
          hide(snippets.success)
        }
        if (snippets.error) {
          hide(snippets.error);
        }
        show(element)
      }

      if (snippets.reset) {
        addEventListener(snippets.reset, 'click', handleReset)
      }

      function getFormData() {
        const formData = new FormData(form)
        const data = {}
        formData.entries().forEach(pair => {
          data[pair[0]] = pair[1]
        })
        return data
      }

      async function handleSubmit(event) {
        event.preventDefault()
        const data = getFormData()
        const response = await plugin.push(data)
        if (behaviour === 'hide') {
          hide(element)
        }
        show(snippets.reset)
        if (response.status.code === 200) {
          show(snippets.success)
          hide(snippets.error)
        } else {
          hide(snippets.success)
          show(snippets.error)
        }
        DMF.refresh()
      }

      addEventListener(form, 'submit', handleSubmit)
    }

    function initAllElements() {
      reset()
      DMF.getAllPluginInstancesWithElements(
        'distributor'
      ).forEach(({ element, plugin }) => {
        initElement(element, plugin)
      })
    }

    initAllElements()
    DMF.onRefresh(initAllElements);
  }

  function init(event) {
    document.removeEventListener('dmf-ready', init)
    start(event.detail.DMF)
  }

  document.addEventListener('dmf-ready', init)
  document.dispatchEvent(new Event('dmf-request-ready'))
})()
