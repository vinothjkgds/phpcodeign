<!DOCTYPE html>
<html lang="<?= esc(service('request')->getLocale(), 'attr') ?>">
   <head>
      <?php include("theme/metaheader.php") ?>
   </head>
   <body class="sidebar-fixed">
      <div class="container-scroller">
         <!-- partial:../../partials/_navbar.html -->
         <?php include("theme/header.php") ?>
         <!-- partial -->
         <div class="container-fluid page-body-wrapper">
            <?php include("theme/right-sidebar.php") ?>
            <!-- Sidebar Navigation Menu -->
            <?php include("theme/sidebar-navigation.php") ?>
            <!-- Body Content -->
            <div class="main-panel">
               <div class="content-wrapper">
                  <?php if(isset($body_content)): ?>
                     <?php if(session()->get('auth_usertype') == 'saas'){ ?>
                     <?php include("admin/pages/".$body_content.".php") ?>
                     <?php }else{ ?> 
                     <?php include("pages/".$body_content.".php") ?>
                     <?php } ?>
                  <?php endif ?>
               </div>
               <?php include("theme/footer.php") ?>
            </div>
            <!-- Body Content ends -->
         </div>
      </div>
      <!-- container-scroller -->
      <?php include("theme/metafooter.php") ?>
   </body>
</html>