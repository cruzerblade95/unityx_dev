/**
 *  Pages Authentication - Reset Password
 */
'use strict';

document.addEventListener('DOMContentLoaded', function () {
  (() => {
    const formAuthentication = document.querySelector('#formAuthentication');

    // Form validation for reset password form
    if (formAuthentication && typeof FormValidation !== 'undefined') {
      FormValidation.formValidation(formAuthentication, {
        fields: {
          password: {
            validators: {
              notEmpty: {
                message: 'Please enter your password'
              },
              stringLength: {
                min: 8,
                message: 'Password must be at least 8 characters'
              }
            }
          },
          password_confirmation: {
            validators: {
              notEmpty: {
                message: 'Please confirm password'
              },
              identical: {
                compare: () => {
                  const passwordField = formAuthentication.querySelector('[name="password"]');
                  return passwordField ? passwordField.value : '';
                },
                message: 'The password and its confirmation do not match'
              },
              stringLength: {
                min: 8,
                message: 'Password must be at least 8 characters'
              }
            }
          }
        },
        plugins: {
          trigger: new FormValidation.plugins.Trigger(),
          bootstrap5: new FormValidation.plugins.Bootstrap5({
            eleValidClass: '',
            rowSelector: '.form-control-validation'
          }),
          submitButton: new FormValidation.plugins.SubmitButton(),
          defaultSubmit: new FormValidation.plugins.DefaultSubmit(),
          autoFocus: new FormValidation.plugins.AutoFocus()
        },
        init: instance => {
          instance.on('plugins.message.placed', e => {
            if (e.element.parentElement.classList.contains('input-group')) {
              e.element.parentElement.insertAdjacentElement('afterend', e.messageElement);
            }
          });
        }
      });
    }
  })();
});
