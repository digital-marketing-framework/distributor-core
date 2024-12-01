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

  function initPushPlugin(plugin) {
    if (plugin.settings.manualProcessing) {
      return
    }

    const form = plugin.element.closest('form')
    const behaviour = plugin.settings.behaviour

    if (form === null) {
      return
    }

    function handleReset(event) {
      event.preventDefault()
      plugin.hide('reset')
      plugin.hide('success')
      plugin.hide('error')
      plugin.show()
    }

    plugin.on('click', handleReset, 'reset')

    async function handleSubmit(event) {
      event.preventDefault()

      if (DMF.getPluginAttribute(form, 'disabled')) {
        return
      }

      const submitter = event.submitter
      if (submitter) {
        if (DMF.getPluginAttribute(submitter, 'disabled')) {
          return
        }

        const name = DMF.getPluginAttribute(submitter, 'name')
        const value = DMF.getPluginAttribute(submitter, 'value')
        if (name && value) {
          const input = form.querySelector('input[name="' + name + '"]')
          if (input !== null) {
            input.value = value
          }
        }
      }

      const data = DMF.getFormData(form)
      DMF.trigger(form, EVENT_FORM_SUBMIT, data)

      form.classList.add(CLASS_SUBMITTING)
      const response = await plugin.push(data)
      form.classList.remove(CLASS_SUBMITTING)

      if (behaviour === 'hide') {
        plugin.hide()
        plugin.show('reset')
      }

      if (response.status.code === 200) {
        DMF.trigger(form, EVENT_FORM_SUBMIT_SUCCESS, data)
        plugin.show('success')
        plugin.hide('error')
      } else {
        DMF.trigger(form, EVENT_FORM_SUBMIT_ERROR, data)
        plugin.hide('success')
        plugin.show('error')
      }

      DMF.refresh()
    }

    plugin.on('submit', handleSubmit, form)
  }

  DMF.plugins('distributor', initPushPlugin)
})()
