/**
 * User Deposit Promo Bonuses
 */

'use strict';

$(function () {
  console.log('User deposit promo bonuses script loaded');
  console.log('jQuery version:', $.fn.jquery);
  console.log('SweetAlert2 available:', typeof Swal !== 'undefined');

  var claimBonusId = null;

  // Countdown timers
  function updateCountdowns() {
    $('.countdown').each(function () {
      var $this = $(this);
      var expiresAt = new Date($this.data('expires'));
      var now = new Date();
      var diff = expiresAt.getTime() - now.getTime();

      if (diff <= 0) {
        $this.text('Expired');
        $this.parent().removeClass('alert-warning').addClass('alert-danger');
        var $claimBtn = $this.closest('.card').find('.claim-bonus-btn');
        $claimBtn.prop('disabled', true).removeClass('btn-success').addClass('btn-secondary').text('Expired');
        return;
      }

      var days = Math.floor(diff / (1000 * 60 * 60 * 24));
      var hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
      var minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
      var seconds = Math.floor((diff % (1000 * 60)) / 1000);

      var timeText = '';
      if (days > 0) {
        timeText = days + 'd ' + hours + 'h ' + minutes + 'm';
      } else if (hours > 0) {
        timeText = hours + 'h ' + minutes + 'm ' + seconds + 's';
      } else {
        timeText = minutes + 'm ' + seconds + 's';
      }

      $this.text('Time remaining: ' + timeText);
    });
  }

  // Update countdowns every second
  updateCountdowns();
  setInterval(updateCountdowns, 1000);

  // Claim bonus button click
  $('.claim-bonus-btn').on('click', function () {
    claimBonusId = $(this).data('bonus-id');
    var bonusAmount = $(this).data('bonus-amount');

    $('#claimAmountDisplay').text('$' + parseFloat(bonusAmount).toFixed(2));

    // Use jQuery Bootstrap modal
    $('#claimBonusModal').modal('show');
  });

  // Confirm claim
  $('#confirmClaimBtn').on('click', function () {
    if (!claimBonusId) return;

    var $btn = $(this);
    $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Claiming...');

    $.ajax({
      url: window.userBonusClaimRoute.replace(':id', claimBonusId),
      type: 'POST',
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      success: function (response) {
        if (response.success) {
          // Use jQuery Bootstrap modal to hide
          $('#claimBonusModal').modal('hide');

          Swal.fire({
            icon: 'success',
            title: 'Bonus Claimed!',
            html: `
                            <p>Your bonus has been successfully claimed!</p>
                            <div class="mt-3">
                                <strong class="text-success fs-5">+$${parseFloat(response.bonus_amount).toFixed(2)}</strong>
                            </div>
                            <p class="mt-2 text-muted">Added to your deposit wallet</p>
                        `,
            timer: 5000,
            timerProgressBar: true
          }).then(() => {
            // Reload page to update the bonuses list
            window.location.reload();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Claim Failed',
            text: response.message
          });
        }
      },
      error: function (xhr) {
        var errorMessage = 'Failed to claim bonus';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          errorMessage = xhr.responseJSON.message;
        }

        if (typeof Swal !== 'undefined') {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorMessage
          });
        } else {
          alert('Error: ' + errorMessage);
        }
      },
      complete: function () {
        $btn.prop('disabled', false).html('<i class="fas fa-hand-holding-usd"></i> Claim Now');
        claimBonusId = null;
      }
    });
  });

  // Reset modal when closed
  $('#claimBonusModal').on('hidden.bs.modal', function () {
    claimBonusId = null;
    $('#confirmClaimBtn').prop('disabled', false).html('<i class="fas fa-hand-holding-usd"></i> Claim Now');
  });
});
