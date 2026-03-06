<script>
    $( document ).ready(function() {
        $('.permission-checkbox').each(function() {
            let module = jQuery(this).attr('module');
            selectModule(module);
        });
    });
  jQuery(document).on('change', '.permission-module-checkbox', function() {
            let id = jQuery(this).attr('id');

            if (jQuery(this).is(':checked')) {
                jQuery('.'+id).prop('checked', true);
            } else {
                jQuery('.'+id).prop('checked', false);
            }
        });
        jQuery(document).on('change', '.permission-checkbox', function() {
            let module = jQuery(this).attr('module');
             selectModule(module);

        });
        function selectModule(module){
            if ($('.permission-checkbox.'+module+'-module:checked').length > 0) {
                    jQuery('#'+module+'-module').prop('checked', true);
                } else {
                    jQuery('#'+module+'-module').prop('checked', false);
                }
        }



  document.addEventListener('DOMContentLoaded', function () {
      const isAdminCheckbox = document.getElementById('is-administrator-role');
      const isVendorCheckbox = document.getElementById('is-vendor-role');
      const assignButton = document.querySelector('button[type="submit"]');
      const errorMessage = document.createElement('div');
      errorMessage.style.color = 'red';
      errorMessage.style.marginTop = '10px';
      errorMessage.style.fontSize = '14px';
      errorMessage.textContent = 'Please select only one role (Admin or Vendor).';
      errorMessage.style.display = 'none';

      const form = document.querySelector('form');
      form.appendChild(errorMessage);

      function togglePermissions() {
          const isAdminChecked = isAdminCheckbox.checked;
          const isVendorChecked = isVendorCheckbox.checked;

          const modulePermissionRows = document.querySelectorAll('.module-permission-row');
          if (isAdminChecked) {
              modulePermissionRows.forEach(row => {
                  const module = row.getAttribute('data-module');
                  if (module === 'Order Management' || module === 'Vendor User Management'|| module === 'Integration Management'|| module === 'Page Management'|| module === 'CMS Management' ) {
                      row.style.display = 'none';
                  } else {
                      row.style.display = 'block';
                  }
              });
          } else if (isVendorChecked) {
              const vendorModules = ['Order Management', 'Vendor User Management', 'User Permission', 'Wallet Management'];
              modulePermissionRows.forEach(row => {
                  const module = row.getAttribute('data-module');
                  if (vendorModules.includes(module)) {
                      row.style.display = 'block';
                  } else {
                      row.style.display = 'none';
                  }
              });
          } else {
              modulePermissionRows.forEach(row => row.style.display = 'none');
          }
      }

      function checkPermissionsCombination() {
          const isAdminChecked = isAdminCheckbox.checked;
          const isVendorChecked = isVendorCheckbox.checked;

          if (isAdminChecked && isVendorChecked) {
              errorMessage.style.display = 'block';
              assignButton.disabled = true;
          } else {
              errorMessage.style.display = 'none';
              assignButton.disabled = false;
          }
      }

      function handleSelectAllPermissions() {
          const isAdminChecked = isAdminCheckbox.checked;
          const isVendorChecked = isVendorCheckbox.checked;
          const selectAllCheckbox = document.getElementById('select-all-permissions-admin');

          selectAllCheckbox.addEventListener('click', function () {
              const isChecked = selectAllCheckbox.checked;
              if (isAdminChecked) {
                  document.querySelectorAll('.admin-permission-checkbox').forEach(function (permissionCheckbox) {
                      permissionCheckbox.checked = isChecked;
                  });
              } else if (isVendorChecked) {
                  document.querySelectorAll('.vendor-permission-checkbox').forEach(function (permissionCheckbox) {
                      permissionCheckbox.checked = isChecked;
                  });
              }
          });
      }


      isAdminCheckbox.addEventListener('click', function () {
          if (this.checked) {
              isVendorCheckbox.checked = false;
              unselectAdminPermissions();
          }
          togglePermissions();
          checkPermissionsCombination();
          handleSelectAllPermissions();
      });


      isVendorCheckbox.addEventListener('click', function () {
          if (this.checked) {
              isAdminCheckbox.checked = false;
              unselectAdminPermissions();
          }
          togglePermissions();
          checkPermissionsCombination();
          handleSelectAllPermissions();
      });

      function unselectAdminPermissions() {
          const adminPermissions = document.querySelectorAll('.permission-checkbox-row input[type="checkbox"]:checked');
          const vendorPermissions = document.querySelectorAll('.permission-module-checkbox input[type="checkbox"]:checked');
          adminPermissions.forEach(permission => {
              permission.checked = false;
          });

          vendorPermissions.forEach(permission => {
              permission.checked = false;
          });


      }

      togglePermissions();
      checkPermissionsCombination();
      handleSelectAllPermissions();
  });


    document.querySelector('form').addEventListener('submit', function(e) {
        let isAdminRole = document.getElementById('is-administrator-role').checked;
        let isVendorRole = document.getElementById('is-vendor-role').checked;

        let roleTypeInput = document.createElement('input');
        roleTypeInput.type = 'hidden';
        roleTypeInput.name = 'role_type';
        roleTypeInput.value = isAdminRole ? 1 : (isVendorRole ? 2 : null);

        this.appendChild(roleTypeInput);
    });




  document.getElementById('select-all-permissions-admin').addEventListener('click', function () {
      const isChecked = this.checked;
      console.log('Select All Permissions:', isChecked);

      const isAdminChecked = document.getElementById('is-administrator-role').checked;
      const isVendorChecked = document.getElementById('is-vendor-role').checked;

      if (isAdminChecked && isVendorChecked) {
          alert("Error: Please select either Administrator or Vendor, not both.");
          console.error("Error: Both Administrator and Vendor cannot be selected simultaneously.");
          document.getElementById('is-administrator-role').checked = false;
          document.getElementById('is-vendor-role').checked = false;
          return;
      }

      if (isAdminChecked) {
          console.log('Admin role selected');
          selectAdminPermissions(isChecked);
      } else if (isVendorChecked) {
          console.log('Vendor role selected');
          selectVendorPermissions(isChecked);
      }
  });

  document.getElementById('is-administrator-role').addEventListener('change', function () {
      const isAdminChecked = this.checked;
      const vendorCheckbox = document.getElementById('is-vendor-role');

      if (isAdminChecked && vendorCheckbox.checked) {
          alert("Error: Only one role can be selected at a time.");
          console.error("Error: Administrator and Vendor cannot be selected simultaneously.");
          vendorCheckbox.checked = false;
      }
  });

  document.getElementById('is-vendor-role').addEventListener('change', function () {
      const isVendorChecked = this.checked;
      const adminCheckbox = document.getElementById('is-administrator-role');

      if (isVendorChecked && adminCheckbox.checked) {
          alert("Error: Only one role can be selected at a time.");
          console.error("Error: Administrator and Vendor cannot be selected simultaneously.");
          adminCheckbox.checked = false;
      }
  });

  function selectAdminPermissions(isChecked) {
      console.log('Selecting Admin Permissions:', isChecked);
      const adminModules = [
          "admin user management",
          "promo code",
          "price rule",
          "b2c configuration",
          "wallet management",
          "web configuration management",
          "report management",
          "page management",
          "subscription management",
          "user permission"
      ];

      adminModules.forEach(module => {
          const moduleClass = '.' + module.replace(/\s+/g, '-').toLowerCase() + '-module';
          console.log(`Looking for checkboxes with class: ${moduleClass}`);

          document.querySelectorAll(moduleClass).forEach(permissionCheckbox => {
              if (permissionCheckbox) {
                  permissionCheckbox.checked = isChecked;
                  console.log(`Checkbox ${moduleClass} set to ${isChecked}`);
              } else {
                  console.log(`Checkbox not found for ${moduleClass}`);
              }
          });

          const moduleCheckboxId = module.replace(/\s+/g, '-').toLowerCase() + '-module';
          const moduleCheckbox = document.getElementById(moduleCheckboxId);
          console.log(`Looking for checkbox with ID: ${moduleCheckboxId}`);
          if (moduleCheckbox) {
              moduleCheckbox.checked = isChecked;
              console.log(`Module checkbox for ${module} set to ${isChecked}`);
          } else {
              console.log(`Module checkbox not found for ${module}`);
          }
      });
  }

  function selectVendorPermissions(isChecked) {
      console.log('Selecting Vendor Permissions:', isChecked);
      const vendorModules = [
          "vendor user management",
          "user permission",
          "wallet management",
          "order management"
      ];

      vendorModules.forEach(module => {
          const moduleClass = '.' + module.replace(/\s+/g, '-').toLowerCase() + '-module';
          console.log(`Looking for checkboxes with class: ${moduleClass}`);

          document.querySelectorAll(moduleClass).forEach(permissionCheckbox => {
              if (permissionCheckbox) {
                  permissionCheckbox.checked = isChecked;
                  console.log(`Checkbox ${moduleClass} set to ${isChecked}`);
              } else {
                  console.log(`Checkbox not found for ${moduleClass}`);
              }
          });

          const moduleCheckboxId = module.replace(/\s+/g, '-').toLowerCase() + '-module';
          const moduleCheckbox = document.getElementById(moduleCheckboxId);
          console.log(`Looking for checkbox with ID: ${moduleCheckboxId}`);
          if (moduleCheckbox) {
              moduleCheckbox.checked = isChecked;
              console.log(`Module checkbox for ${module} set to ${isChecked}`);
          } else {
              console.log(`Module checkbox not found for ${module}`);
          }
      });
  }




  document.querySelectorAll('.permission-module-checkbox').forEach(moduleCheckbox => {
      moduleCheckbox.addEventListener('change', function () {
          let module = this.id.replace('-module', '');
          console.log(`Module checkbox ${module} changed to ${this.checked}`);

          document.querySelectorAll('.' + module).forEach(permissionCheckbox => {
              if (permissionCheckbox) {
                  permissionCheckbox.checked = this.checked;
                  console.log(`Permission checkbox for ${module} set to ${this.checked}`);
              }
          });
      });
  });



    isAdminCheckbox.addEventListener('click', function () {
        if (this.checked) {
            isVendorCheckbox.checked = false;
            unselectAllPermissions();
            selectAdminPermissions(true);
        } else {
            unselectAllPermissions();
        }
        togglePermissions();
        checkPermissionsCombination();
        handleSelectAllPermissions();
    });

    isVendorCheckbox.addEventListener('click', function () {
        if (this.checked) {
            isAdminCheckbox.checked = false;
            unselectAllPermissions();
            selectVendorPermissions(true);
        } else {
            unselectAllPermissions();
        }
        togglePermissions();
        checkPermissionsCombination();
        handleSelectAllPermissions();
    });

    function unselectAllPermissions() {
        document.querySelectorAll('.admin-permission-checkbox, .vendor-permission-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });
        console.log("All permissions have been unselected.");
    }

    function selectAdminPermissions(isChecked) {
        console.log('Selecting Admin Permissions:', isChecked);
        const adminModules = [
            "admin user management",
            "promo code",
            "price rule",
            "b2c configuration",
            "wallet management",
            "web configuration management",
            "report management",
            "page management",
            "subscription management",
            "user permission"
        ];

        adminModules.forEach(module => {
            const moduleClass = '.' + module.replace(/\s+/g, '-').toLowerCase() + '-module';
            document.querySelectorAll(moduleClass).forEach(permissionCheckbox => {
                if (permissionCheckbox) {
                    permissionCheckbox.checked = isChecked;
                    console.log(`Checkbox ${moduleClass} set to ${isChecked}`);
                }
            });

            const moduleCheckboxId = module.replace(/\s+/g, '-').toLowerCase() + '-module';
            const moduleCheckbox = document.getElementById(moduleCheckboxId);
            if (moduleCheckbox) {
                moduleCheckbox.checked = isChecked;
                console.log(`Module checkbox for ${module} set to ${isChecked}`);
            }
        });
    }

    function selectVendorPermissions(isChecked) {
        console.log('Selecting Vendor Permissions:', isChecked);
        const vendorModules = [
            "vendor user management",
            "user permission",
            "wallet management",
            "order management"
        ];

        vendorModules.forEach(module => {
            const moduleClass = '.' + module.replace(/\s+/g, '-').toLowerCase() + '-module';
            document.querySelectorAll(moduleClass).forEach(permissionCheckbox => {
                if (permissionCheckbox) {
                    permissionCheckbox.checked = isChecked;
                    console.log(`Checkbox ${moduleClass} set to ${isChecked}`);
                }
            });

            const moduleCheckboxId = module.replace(/\s+/g, '-').toLowerCase() + '-module';
            const moduleCheckbox = document.getElementById(moduleCheckboxId);
            if (moduleCheckbox) {
                moduleCheckbox.checked = isChecked;
                console.log(`Module checkbox for ${module} set to ${isChecked}`);
            }
        });
    }

</script>
