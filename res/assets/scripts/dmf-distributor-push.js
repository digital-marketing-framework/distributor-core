;(async function () {
  const EVENT_FORM_SUBMIT = 'dmf-form-submit'
  const EVENT_FORM_SUBMIT_SUCCESS = 'dmf-form-submit-success'
  const EVENT_FORM_SUBMIT_ERROR = 'dmf-form-submit-error'
  const EVENT_FORM_SUBMIT_RESET = 'dmf-form-reset'
  const EVENT_FORM_REDIRECT = 'dmf-form-redirect'

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
      plugin.markAsCleared()
      plugin.show()
    }

    plugin.on('click', handleReset, plugin.settings.snippets.RESET)

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
        plugin.show(plugin.settings.snippets.RESET)
      }

      if (response.status.code === 200) {
        DMF.trigger(form, EVENT_FORM_SUBMIT_SUCCESS, data)
        plugin.markAsSucceeded()

        const redirectUrl = response.response && response.response.redirect
        if (redirectUrl) {
          const redirectEvent = new CustomEvent(EVENT_FORM_REDIRECT, {
            detail: { redirect: redirectUrl, data: data },
            cancelable: true,
          })
          form.dispatchEvent(redirectEvent)
          if (!redirectEvent.defaultPrevented) {
            window.location.href = redirectUrl
            return
          }
        }
      } else {
        const code = response.status.code
        const isExpectedError = code >= 400 && code < 500
        if (!isExpectedError) {
          console.error('Push failed:', response)
        }
        DMF.trigger(form, EVENT_FORM_SUBMIT_ERROR, data)
        plugin.markAsFailed(response.status.message)
      }

      DMF.refresh()
    }

    plugin.on('submit', handleSubmit, form)
  }

  DMF.plugins('distributor', initPushPlugin)
})()
