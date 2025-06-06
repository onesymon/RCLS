<!-- Edit Member Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
    <div class="modal-content border-primary">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="fas fa-user-edit"></i> Edit Member Profile</h5>
        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
      </div>

      <form id="editProfileForm" method="post" action="" enctype="multipart/form-data">
        <div class="modal-body">
          <!-- Copy of card-body form fields -->
          <h5 class="text-primary"><i class="fas fa-id-card"></i> Personal Information</h5>
          <div class="form-group">
            <label for="fullname">Full Name</label>
            <input type="text" class="form-control" id="fullname" name="fullname" required value="<?php echo $memberDetails['fullname']; ?>">
          </div>
          <div class="form-group">
            <label for="dob">Date of Birth</label>
            <input type="date" class="form-control" id="dob" name="dob" required value="<?php echo $memberDetails['dob']; ?>">
          </div>
          <div class="form-group">
            <label for="gender">Gender</label>
            <select class="form-control" id="gender" name="gender" required>
              <option value="Male" <?php echo ($memberDetails['gender'] == 'Male') ? 'selected' : ''; ?>>Male</option>
              <option value="Female" <?php echo ($memberDetails['gender'] == 'Female') ? 'selected' : ''; ?>>Female</option>
              <option value="Other" <?php echo ($memberDetails['gender'] == 'Other') ? 'selected' : ''; ?>>Other</option>
            </select>
          </div>

          <hr>
          <h5 class="text-primary"><i class="fas fa-phone-alt"></i> Contact Information</h5>
          <div class="form-group">
            <label for="contactNumber">Contact Number</label>
            <input type="tel" class="form-control" id="contactNumber" name="contactNumber"
              pattern="^09\d{9}$" maxlength="11" inputmode="numeric"
              oninput="this.value = this.value.replace(/[^0-9]/g, '')"
              required value="<?php echo $memberDetails['contact_number']; ?>">
            <small class="form-text text-muted">Must start with 09 and be exactly 11 digits.</small>
          </div>
          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" class="form-control" id="email" name="email" required value="<?php echo $memberDetails['email']; ?>">
          </div>
          <div class="form-group">
            <label for="address">Home Address</label>
            <input type="text" class="form-control" id="address" name="address" required value="<?php echo $memberDetails['address']; ?>">
          </div>

          <hr>
          <h5 class="text-primary"><i class="fas fa-briefcase"></i> Occupation & Photo</h5>
          <div class="form-group">
            <label for="occupation">Occupation</label>
            <input type="text" class="form-control" id="occupation" name="occupation" required value="<?php echo $memberDetails['occupation']; ?>">
          </div>
          <div class="form-group">
            <label for="photo">Profile Photo</label>
            <input type="file" class="form-control-file" id="photo" name="photo" accept="image/*" onchange="previewPhoto(event)">
            <small class="text-muted">Leave blank if you don’t want to change the photo.</small><br>
            <img id="photoPreview" src="/rotary/uploads/member_photos/<?php echo $memberDetails['photo']; ?>" alt="Current Photo" style="max-width: 150px; margin-top: 10px;">
          </div>

          <hr>
          <div class="mb-2 d-flex justify-content-between align-items-center">
            <h5 class="text-primary mb-0"><i class="fas fa-lock"></i> Change Password (Optional)</h5>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="collapse" data-target="#passwordCollapse">Toggle</button>
          </div>
          <div id="passwordCollapse" class="collapse">
            <div class="form-row">
              <div class="form-group col-md-4">
                <label for="currentPassword">Current Password</label>
                <input type="password" class="form-control" id="currentPassword" name="currentPassword">
              </div>
              <div class="form-group col-md-4">
                <label for="newPassword">New Password</label>
                <input type="password" class="form-control" id="newPassword" name="newPassword">
              </div>
              <div class="form-group col-md-4">
                <label for="confirmPassword">Confirm Password</label>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword">
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
        </div>
      </form>
    </div>
  </div>
</div>
