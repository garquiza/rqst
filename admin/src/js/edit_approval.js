// Function to trigger Swal for editing approval details
function updateApproval (prId, currentApprover, currentStatus) {
  Swal.fire ({
    title: 'Edit Approval Details',
    html: `
                <div class="mb-3 text-start">
                    <label for="swal-approver" class="form-label">Approved By:</label>
                    <input type="text" id="swal-approver" class="form-control" value="${currentApprover}" placeholder="Enter approver name">
                </div>
                <div class="mb-3 text-start">
                    <label for="swal-status" class="form-label">Status:</label>
                    <select id="swal-status" class="form-select">
                        <option value="Pending" ${currentStatus === 'Pending' ? 'selected' : ''}>Pending</option>
                        <option value="Approved" ${currentStatus === 'Approved' ? 'selected' : ''}>Approved</option>
                        <option value="Declined" ${currentStatus === 'Declined' ? 'selected' : ''}>Declined</option>
                    </select>
                </div>
            `,
    showCancelButton: true,
    confirmButtonText: 'Save Changes',
    cancelButtonText: 'Cancel',
    preConfirm: () => {
      const approver = document.getElementById ('swal-approver').value.trim ();
      const status = document.getElementById ('swal-status').value;

      if (!approver) {
        Swal.showValidationMessage ('Approver name is required.');
        return false;
      }

      return {
        approver,
        status,
      };
    },
  }).then (result => {
    if (result.isConfirmed) {
      const {approver, status} = result.value;

      // Send POST request to update the database
      fetch ('src/process/update_pr.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify ({
          pr_id: prId,
          approver: approver,
          status: status,
        }),
      })
        .then (response => response.json ())
        .then (data => {
          if (data.success) {
            Swal.fire (
              'Updated!',
              'Approval details updated successfully.',
              'success'
            ).then (() => {
              location.reload (); // Reload page to reflect changes
            });
          } else {
            Swal.fire ('Error!', 'Failed to update approval details.', 'error');
          }
        })
        .catch (error => {
          console.error ('Error:', error);
          Swal.fire (
            'Error!',
            'An error occurred while updating approval details.',
            'error'
          );
        });
    }
  });
}
