<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= SITE_NAME ?></title>
  <link rel="stylesheet" href="<?= base_url() ?>assets/vendors/mdi/css/materialdesignicons.min.css">
  <link rel="stylesheet" href="<?= base_url() ?>assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="<?= base_url() ?>assets/css/vertical-layout-light/style.css">
  <link rel="shortcut icon" href="<?= base_url() ?>assets/images/favicon.png" />
</head>

<body class="sidebar-fixed">
  <div class="container-scroller">
    <div class="container-fluid page-body-wrapper full-page-wrapper">
      <div class="content-wrapper d-flex align-items-stretch auth auth-img-bg">
        <div class="row flex-grow">
          <div class="col-lg-6 d-flex align-items-center justify-content-center">
            <div class="auth-form-transparent text-left p-3">
              <div class="text-center">
                <div class="brand-logo">
                  <img src="<?= base_url() ?>assets/images/logo-black.svg" alt="logo">
                </div>
                <h4>Welcome back!</h4>
                <h6 class="fw-light">Happy to see you again!</h6>
              </div>

              <!-- Display Validation Errors -->
              <?php if(isset($validation)): ?>
                  <div class="alert alert-danger 1"><?= $validation->listErrors() ?></div>
              <?php endif; ?>
              <?php if(isset($error)): ?>
                  <div class="alert alert-danger 2"><?= $error ?></div>
              <?php endif; ?>
              <div id="jQueryValidationError"> </div>

              <form id="authLoginForm" class="pt-3" method="post" action="<?= base_url('/login') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                  <label for="authEmail">Email</label>
                  <div class="input-group">
                    <div class="input-group-prepend bg-transparent">
                      <span class="input-group-text bg-transparent border-right-0">
                        <i class="mdi mdi-account-outline text-primary"></i>
                      </span>
                    </div>
                    <input type="email" class="form-control form-control-lg border-left-0" 
                           id="authEmail" name="email" placeholder="Email" value="<?= set_value('email') ?>" required>
                  </div>
                </div>

                <div class="form-group">
                  <label for="authPassword">Password</label>
                  <div class="input-group">
                    <div class="input-group-prepend bg-transparent">
                      <span class="input-group-text bg-transparent border-right-0">
                        <i class="mdi mdi-lock-outline text-primary"></i>
                      </span>
                    </div>
                    <input type="password" class="form-control form-control-lg border-left-0" 
                           id="authPassword" name="password" placeholder="Password" required>                        
                  </div>
                </div>

                <div class="my-2 d-flex justify-content-between align-items-center">
                  <div class="form-check">
                    <label class="form-check-label text-muted">
                      <input type="checkbox" class="form-check-input" name="remember">
                      Keep me signed in
                    </label>
                  </div>
                  <a href="<?= base_url('cms/forgotpassword') ?>" class="auth-link text-black">Forgot password?</a>
                </div>
                <div class="my-3 d-grid gap-2">
                  <button type="submit" id="submitBtn" class="btn btn-primary btn-lg fw-medium auth-form-btn">LOGIN</button>
                </div>

                
              </form>
            </div>
          </div>

          <div class="col-lg-6 login-half-bg d-flex flex-row">
            <p class="text-white fw-medium text-center flex-grow align-self-end">Copyright &copy; 2024 All rights reserved.</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- plugins:js -->
  <script src="<?= base_url() ?>assets/vendors/js/vendor.bundle.base.js"></script>
  <script src="<?= base_url() ?>assets/js/off-canvas.js"></script>
  <script src="<?= base_url() ?>assets/js/hoverable-collapse.js"></script>
  <script src="<?= base_url() ?>assets/js/template.js"></script>
  <script src="<?= base_url() ?>assets/js/settings.js"></script>
  <script src="<?= base_url() ?>assets/js/todolist.js"></script>

  <!-- jQuery Validation JS -->
  <script src="<?= base_url() ?>assets/vendors/jquery-validation/jquery.validate.min.js"></script>
  <script type="text/javascript">
  (function($){
      'use strict';
      $(function(){
          // Initialize form validation
          $("#authLoginForm").validate({
              rules: {
                  email: { required:true, email:true },
                  password: "required"
              },
              messages: {
                  email: "Please Enter a valid email address",
                  domain: "Please Enter Password",
              },
              errorPlacement: function(label, element){
                  label.addClass('mt-2 text-danger w-100');
                  label.insertAfter(element);
              },
              highlight: function(element){
                  $(element).parent().addClass('has-danger');
                  $(element).addClass('form-control-danger');
              },
              submitHandler: function(form){
                  // Disable button to prevent multiple submissions
                  $('#submitBtn').attr('disabled', true).val('Login...');
                  let formData = new FormData(form);

                  // AJAX form submission
                  $.ajax({
                      url: $(form).attr('action'),
                      type: 'POST',
                      data: formData,
                      processData: false,
                      contentType: false,
                      success: function(response){
                          if(response.status){
                              window.location.href = response.redirect;
                          } else {
                              $("#jQueryValidationError").html('<div class="alert alert-danger">'+response.error+'</div>');
                              $('#submitBtn').attr('disabled', false).val('Login');
                          }
                      },
                      error: function(xhr){
                          let resp = xhr.responseJSON;
                          $("#jQueryValidationError").html('<div class="alert alert-danger">'+resp?.message || "Something went wrong! Status: " + xhr.status+'</div>');
                          $('#submitBtn').attr('disabled', false).val('Login');
                      }
                  });
                  return false; // Prevent default form submission
              }
          });
      });
  })(jQuery);
  </script>

</body>
</html>
