;(async function () {

  async function loadDMF() {
    setTimeout(() => {
      document.dispatchEvent(new Event('dmf-request-ready'))
    }, 0)
    return new Promise(resolve => {
      document.addEventListener('dmf-ready', event => {
        resolve(event.detail.DMF)
      })
    })
  }

  const DMF = await loadDMF()
  const prefix = DMF.settings.prefix
  const listeners = []

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

  function initElement(plugin) {
    const form = plugin.element.closest('form')
    const behaviour = plugin.settings.behaviour
    const snippets = plugin.getSnippets()

    if (form === null) {
      return
    }

    function handleReset(event) {
      event.preventDefault()
      if (snippets.reset) {
        plugin.hide(snippets.reset)
      }
      if (snippets.success) {
        plugin.hide(snippets.success)
      }
      if (snippets.error) {
        plugin.hide(snippets.error);
      }
      plugin.show()
    }

    if (snippets.reset) {
      addEventListener(snippets.reset, 'click', handleReset)
    }

    function getFormData() {
      const formData = new FormData(form)
      const data = {}
      for (let [name, value] of formData.entries()) {
        data[name] = value
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
        plugin.hide()
      }
      plugin.show(snippets.reset)
      if (response.status.code === 200) {
        plugin.show(snippets.success)
        plugin.hide(snippets.error)
      } else {
        plugin.hide(snippets.success)
        plugin.show(snippets.error)
      }
      DMF.refresh()
    }

    addEventListener(form, 'submit', handleSubmit)
  }

  function initAllElements() {
    reset()
    DMF.plugins('distributor').forEach(initElement)
  }

  initAllElements()
  DMF.onRefresh(initAllElements);
})()
