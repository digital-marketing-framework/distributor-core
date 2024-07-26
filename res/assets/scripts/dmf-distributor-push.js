;(async function () {

  async function initDMF() {
    setTimeout(() => {
      document.dispatchEvent(new Event('dmf-request-ready'))
    }, 0)
    return new Promise(resolve => {
      document.addEventListener('dmf-ready', event => {
        resolve(event.detail.DMF)
      })
    })
  }

  const DMF = await initDMF()
  const prefix = DMF.settings.prefix
  const listeners = []

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
    const behaviour = element.dataset[prefix + 'PluginBehaviour'] || ''
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
      for (const pair of formData.entries()) {
        data[pair[0]] = pair[1]
      }
      return data
    }

    async function handleSubmit(event) {
      event.preventDefault()

      const submitter = event.submitter
      if (submitter) {
        const name = submitter.dataset[prefix + 'Name']
        const value = submitter.dataset[prefix + 'Value']
        if (name && value) {
          const input = form.querySelector('input[name="' + name + '"]')
          if (input !== null) {
            input.value = value
          }
        }
      }

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
})()
