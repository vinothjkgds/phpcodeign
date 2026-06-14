<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= SITE_NAME ?> - SaaS Admin</title>
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
                <h4>SaaS Admin Login</h4>
                <h6 class="fw-light">Manage platform and onboarding</h6>
              </div>

              <!-- Display Validation Errors -->
              <?php if(isset($validation)): ?>
                  <div class="alert alert-danger"><?= $validation->listErrors() ?></div>
              <?php endif; ?>
              <?php if(isset($error)): ?>
                  <div class="alert alert-danger"><?= $error ?></div>
              <?php endif; ?>
              <div id="saasLoginError"></div>

              <form id="saasLoginForm" class="pt-3" method="post" action="<?= base_url('/saas/login') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                  <label for="saasEmail">Email</label>
                  <div class="input-group">
                    <div class="input-group-prepend bg-transparent">
                      <span class="input-group-text bg-transparent border-right-0">
                        <i class="mdi mdi-account-outline text-primary"></i>
                      </span>
                    </div>
                    <input type="email" class="form-control form-control-lg border-left-0" 
                           id="saasEmail" name="email" placeholder="Email" value="<?= set_value('email') ?>" required>
                  </div>
                </div>

                <div class="form-group">
                  <label for="saasPassword">Password</label>
                  <div class="input-group">
                    <div class="input-group-prepend bg-transparent">
                      <span class="input-group-text bg-transparent border-right-0">
                        <i class="mdi mdi-lock-outline text-primary"></i>
                      </span>
                    </div>
                    <input type="password" class="form-control form-control-lg border-left-0" 
                           id="saasPassword" name="password" placeholder="Password" required>                        
                  </div>
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
  <script>
  (function($){
      'use strict';
      $(function(){
          $('#saasLoginForm').validate({
              rules: {
                  email: { required: true, email: true },
                  password: { required: true }
              },
              messages: {
                  email: { required: 'Email is required', email: 'Please enter a valid email' },
                  password: { required: 'Password is required' }
              },
              submitHandler: function(form){
                  $('#submitBtn').prop('disabled', true).text('Logging in...');
                  $('#saasLoginError').html('');
                  $.ajax({
                      url: $(form).attr('action'),
                      type: 'POST',
                      data: new FormData(form),
                      processData: false,
                      contentType: false,
                    dataType: 'json',
                      success: function(response){
                      if (response && response.status && response.redirect) {
                              window.location.href = response.redirect;
                              return;
                          }
                      var message = (response && (response.message || response.error)) ? (response.message || response.error) : 'Login failed';
                      $('#saasLoginError').html('<div class="alert alert-danger mt-3">' + message + '</div>');
                          $('#submitBtn').prop('disabled', false).text('LOGIN');
                      },
                      error: function(xhr){
                          var resp = xhr.responseJSON || {};
                      var message = resp.message || resp.error || 'Invalid credentials';
                      $('#saasLoginError').html('<div class="alert alert-danger mt-3">' + message + '</div>');
                          $('#submitBtn').prop('disabled', false).text('LOGIN');
                      }
                  });
                  return false;
              }
          });
      });
  })(jQuery);
  </script>
</body>
</html>
