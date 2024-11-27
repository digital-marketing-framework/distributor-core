;(async function () {
  const EVENT_FORM_SUBMIT = 'dmf-form-submit'
  const EVENT_FORM_SUBMIT_SUCCESS = 'dmf-form-submit-success'
  const EVENT_FORM_SUBMIT_ERROR = 'dmf-form-submit-error'
  const EVENT_FORM_SUBMIT_RESET = 'dmf-form-reset'

  const CLASS_SUBMITTING = 'submitting-form'

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

  function initPushPlugin(plugin) {
    if (plugin.settings.manualProcessing) {
      return
    }

    const form = plugin.element.closest('form')
    const behaviour = plugin.settings.behaviour

    if (form === null) {
      return
    }

    function trigger(name, payload = {}) {
      form.dispatchEvent(new CustomEvent(name, {detail: payload}));
    }

    function handleReset(event) {
      event.preventDefault()
      plugin.hide('reset')
      plugin.hide('success')
      plugin.hide('error')
      plugin.show()
    }

    plugin.on('click', handleReset, 'reset')

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
      trigger(EVENT_FORM_SUBMIT, data)

      form.classList.add(CLASS_SUBMITTING)
      const response = await plugin.push(data)
      form.classList.remove(CLASS_SUBMITTING)

      if (behaviour === 'hide') {
        plugin.hide()
        plugin.show('reset')
      }

      if (response.status.code === 200) {
        trigger(EVENT_FORM_SUBMIT_SUCCESS, data)
        plugin.show('success')
        plugin.hide('error')
      } else {
        trigger(EVENT_FORM_SUBMIT_ERROR, data)
        plugin.hide('success')
        plugin.show('error')
      }

      DMF.refresh()
    }

    plugin.on('submit', handleSubmit, form)
  }

  DMF.plugins('distributor', initPushPlugin)
})()
