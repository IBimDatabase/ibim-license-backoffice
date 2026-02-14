var app = angular.module('licenseManagement', ['720kb.datepicker', 'angularjs-dropdown-multiselect'], function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
}).constant(
    'API_URL', ngHost + 'api/'
);

app.run( function($http, $rootScope, API_URL) {
    $http.defaults.headers.common.Authorization = 'Bearer ' + localStorage.token;

    $rootScope.closeAlert = function(alertType) {
        if (alertType == 'Success')
        {
            angular.element(document.querySelector('.notification-alert.alert-success')).removeClass('show');
        }
        else if (alertType == 'Failure')
        {
            angular.element(document.querySelector('.notification-alert.alert-warning')).removeClass('show');
        }
    };

    $rootScope.getAuthenticatedUser = function () {
        $http({
            url: API_URL + 'get/authenticated-user',
			method: 'GET'
		}).then( function success(response) {
            $rootScope.authenticatedUser = response.data.data;

            // If request from authenticated user then redirect to dashboard
            if (window.location.pathname === '/' || window.location.pathname === '/login')
            {
                window.location.href = '/dashboard';
            }

        }, function error(response) {
            // If request from unauthenticated user then redirect to login page
            if (window.location.pathname !== '/' && window.location.pathname !== '/login')
            {
                window.location.href = '/';
            }
        });
    };

    $rootScope.getAuthenticatedUser();
});

function notificationAlert(alertType, message)
{
    if (alertType == 'Success')
    {
        angular.element(document.querySelector('.notification-alert.alert-success .alert-message')).html(message);
        angular.element(document.querySelector('.notification-alert.alert-success')).addClass('show');

        setTimeout(function() {
            angular.element(document.querySelector('.notification-alert.alert-success')).removeClass('show');
        }, 5000);
    }
    else if (alertType == 'Failure')
    {
        angular.element(document.querySelector('.notification-alert.alert-warning .alert-message')).html(message);
        angular.element(document.querySelector('.notification-alert.alert-warning')).addClass('show');

        setTimeout(function() {
            angular.element(document.querySelector('.notification-alert.alert-warning')).removeClass('show');
        }, 5000);
    }
}

/******** Custom filters ********/

app.filter('licenseKeyHash', function() {
    return function(x) {
        var i, c, txt = "";
        for (i = 0; i < x.length; i++) {
            c = x[i];
            if (i > 4 && i < 15) {
                if (i == 9 || i == 14)
                    c = '-';
                else
                    c = '*';
            }
            txt += c;
        }
        return txt;
    };
});

app.filter('strToDate', function() {
    return function(str) {
        if (str != '' && str != undefined) {
            moment.tz.setDefault('Asia/kolkata');
            var date = moment(str);

            /*
            var istTime = moment.tz( date, 'DD-MM-YYYY hh:mm:ss', 'Asia/kolkata');
            var localTime = istTime.clone().tz(moment.tz.guess());
            console.log('date : ' + date);
            console.log('india : ' + istTime.format("YYYY-MM-DD hh:mm:ss"));
            console.log('aus : ' + localTime.format("YYYY-MM-DD hh:mm:ss"));
            return localTime.format("DD-MM-YYYY hh:mm:ss A");
            */
            return moment.utc(date).local().format();
        }
    };
});

app.filter('textCapitalize', function() {
    return function(str, abbrevation = false) {
        if (str == undefined)
        {
            return '';
        }

        var strArray = str.split(/[\s\_]/i), finalStr = '';
        angular.forEach(strArray, function(value, key) {
            if (abbrevation && (abbrevation == key + 1))
            {
                finalStr += value.toUpperCase() + ' ';
            }
            else
            {
                finalStr += value.charAt(0).toUpperCase() + value.slice(1).toLowerCase() + ' ';
            }
        });
        return finalStr.trim();
    }
});

app.filter('modifyJSON', function() {
    return function(str) {
        let modifyJSON = "";
        if (str != '' && str != undefined)
        {
            console.log(str);
            let dataArr = angular.fromJson(str);
            modifyJSON = 'Browser: ' + dataArr['BROWSER'] + ', OS: ' + dataArr['OS'] + ', IP Address: ' + dataArr['IP_ADDRESS'];
            return modifyJSON;
        }

    };
});


/*** Pagination Directive ***/

app.directive('pagination', function() {
    return {
      restrict: 'E',
      template: '<ul class="pagination justify-content-center">' +
        '<li class="page-item" ng-show="currentPage !== 1"><a href="javascript:void(0)" class="page-link" ng-click="getPaginateData(1)">&laquo;</a></li>' +
        '<li class="page-item" ng-show="currentPage !== 1"><a href="javascript:void(0)" class="page-link" ng-click="getPaginateData(currentPage-1)">&lsaquo; Prev</a></li>' +
        '<li class="page-item" ng-repeat="i in getDisplayRange() track by $index" ng-class="{active: currentPage === i}">' +
        '<a href="javascript:void(0)" class="page-link" ng-click="getPaginateData(i)">{{ i }}</a>' +
        '</li>' +
        '<li class="page-item" ng-if="shouldDisplayEllipsis()"><span class="page-link">...</span></li>' +
        '<li class="page-item" ng-show="currentPage !== totalPages"><a href="javascript:void(0)" class="page-link" ng-click="getPaginateData(currentPage+1)">Next &rsaquo;</a></li>' +
        '<li class="page-item" ng-show="currentPage !== totalPages"><a href="javascript:void(0)" class="page-link" ng-click="getPaginateData(totalPages)">&raquo;</a></li>' +
        '</ul>',
      link: function(scope) {
        scope.shouldDisplayEllipsis = function() {
          return scope.totalPages > 10 && scope.currentPage < scope.totalPages - 8;
        };

        scope.getDisplayRange = function() {
          var start = Math.max(1, scope.currentPage - 4);
          var end = Math.min(start + 9, scope.totalPages);
          return Array.from({ length: end - start + 1 }, (_, i) => start + i);
        };
      }
    };
  });

app.directive('subpagination', function () {
    return {
        restrict: 'E',
        template: '<ul class="pagination justify-content-center">' +
            '<li class="page-item" ng-show="subCurrentPage !== 1"><a href="javascript:void(0)" class="page-link" ng-click="getProductByKey(1)">&laquo;</a></li>' +
            '<li class="page-item" ng-show="subCurrentPage !== 1"><a href="javascript:void(0)" class="page-link" ng-click="getProductByKey(subCurrentPage-1)">&lsaquo; Prev</a></li>' +
            '<li class="page-item" ng-repeat="i in getSubDisplayRange() track by $index" ng-class="{active: subCurrentPage === i}">' +
            '<a href="javascript:void(0)" class="page-link" ng-click="getProductByKey(i)">{{ i }}</a>' +
            '</li>' +
            '<li class="page-item" ng-if="shouldDisplayEllipsis()"><span class="page-link">...</span></li>' +
            '<li class="page-item" ng-show="subCurrentPage !== totalSubPages"><a href="javascript:void(0)" class="page-link" ng-click="getProductByKey(subCurrentPage+1)">Next &rsaquo;</a></li>' +
            '<li class="page-item" ng-show="subCurrentPage !== totalSubPages"><a href="javascript:void(0)" class="page-link" ng-click="getProductByKey(totalSubPages)">&raquo;</a></li>' +
            '</ul>',
        link: function (scope) {
            scope.shouldSubDisplayEllipsis = function () {
                return scope.totalSubPages > 10 && scope.subCurrentPage < scope.totalSubPages - 8;
            };

            scope.getSubDisplayRange = function () {
                var start = Math.max(1, scope.subCurrentPage - 4);
                var end = Math.min(start + 9, scope.totalSubPages);
                return Array.from({ length: end - start + 1 }, (_, i) => start + i);
            };
        }
    };
});


app.directive('compile', ['$compile', function ($compile) {
    return function(scope, element, attrs) {
      scope.$watch(
        function(scope) {
          // watch the 'compile' expression for changes
          return scope.$eval(attrs.compile);
        },
        function(value) {
          // when the 'compile' expression changes
          // assign it into the current DOM
          element.html(value);

          // compile the new DOM and link it to the current
          // scope.
          // NOTE: we only compile .childNodes so that
          // we don't get into infinite loop compiling ourselves
          $compile(element.contents())(scope);
        }
    );
  };
}]);


/******** Controllers ********/

app.controller('authController', function($scope, $http, $document, API_URL) {
    var element = angular.element($document[0].getElementById('login-error-res'));
    element.text('');

    $scope.authenticate = function () {
        $http({
            url: API_URL + 'authenticate',
			method: 'POST',
            data: {
                user_name: $scope.userName,
                password: $scope.password
            },
		}).then( function success(response) {
            if (response.data.code == 200) {
                localStorage.token = response.data.data.token;
                window.location.href = '/dashboard';
            }
        }, function error(response) {
            if (response.status == '500')
            {
                var element = angular.element($document[0].getElementById('login-error-res'));
                element.text('* Server error! might be database connection error!');
            }
            else
            {
                var error = response.data.data.error;
                var element = angular.element($document[0].getElementById('login-error-res'));
                element.text('* ' + error);
            }
        });
    };


    $scope.oldPasswordToggle = true;
    $scope.toggleOldPassword = function() {
        $scope.oldPasswordToggle = !$scope.oldPasswordToggle;
    };

    $scope.passwordToggle = true;
    $scope.togglePassword = function() {
        $scope.passwordToggle = !$scope.passwordToggle;
    };

    $scope.cnfmPasswordToggle = true;
    $scope.toggleCnfmPassword = function() {
        $scope.cnfmPasswordToggle = !$scope.cnfmPasswordToggle;
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'changePasswordForm')
        {
            $scope.changePasswordForm.$touched = false;
            $scope.changePasswordForm.$untouched = true;
            $scope.changePasswordForm.$dirty = false;
            $scope.changePasswordForm.$pristine = true;

            $scope.changePasswordForm.oldPassword.$touched = false;
            $scope.changePasswordForm.oldPassword.$untouched = true;
            $scope.changePasswordForm.oldPassword.$dirty = false;
            $scope.changePasswordForm.oldPassword.$pristine = true;

            $scope.changePasswordForm.password.$touched = false;
            $scope.changePasswordForm.password.$untouched = true;
            $scope.changePasswordForm.password.$dirty = false;
            $scope.changePasswordForm.password.$pristine = true;

            $scope.changePasswordForm.confirmPassword.$touched = false;
            $scope.changePasswordForm.confirmPassword.$untouched = true;
            $scope.changePasswordForm.confirmPassword.$dirty = false;
            $scope.changePasswordForm.confirmPassword.$pristine = true;

            $scope.oldPassword = '';
            $scope.password = '';
            $scope.confirmPassword = '';
        }
    };

    $scope.changePassword = function () {
        var element = angular.element($document[0].getElementById('changePassword-error-res'));
        element.text('');

        $http({
            url: API_URL + 'change/password',
			method: 'POST',
            data: {
                old_password: $scope.oldPassword,
                new_password: $scope.password,
                confirm_password: $scope.confirmPassword,
            },
		}).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#changePasswordModal .btn-close').click();

            // Show the notification
            notificationAlert('Success', 'Password has been <b>changed</b> successfully.');

            $scope.logout();
        }, function error(response) {
            var errors = response.data.data.error, errorData = '';
            var element = angular.element($document[0].getElementById('changePassword-error-res'));
            angular.forEach(errors, function (value, key) {
                errorData += '<div>* ' + value + '</div>';
            });
            element.html(errorData);
        });
    };


    $scope.logout = function () {
        $http({
            url: API_URL + 'logout',
			method: 'POST'
		}).then( function success(response) {
            localStorage.removeItem('token');
            window.location.href = '/';
        }, function error(response) {
            window.location.href = '/';
        });
    };
});

/******** Dashboard Controller ********/

app.controller('dashboardController', function($scope, $http, API_URL) {
    var currentDateString = new Date().toDateString();
    $scope.getCurrentMonthYear = currentDateString.split(' ')[1] + ' ' + currentDateString.split(' ')[3];

    $scope.summary = function() {
        $scope.loading = true;

        $http({
            url: API_URL + 'get/summary',
            method: 'GET'
        }).then( function success(response) {
            var responseData = response.data.data;

            $scope.productCode = responseData.product_count;
            $scope.customerCount = responseData.customer_count;
            $scope.purchasedLicenseCount = responseData.purchased_license_count;
            $scope.cmPurchasedLicenseCount = responseData.cm_purchased_license_count;
            $scope.ordersCount = responseData.orders_count;
            $scope.cmOrdersCount = responseData.cm_orders_count;

            $scope.loading = false;
        }, function error(response) {
            $scope.loading = false;
        });
    }

    $scope.todayPurchases = function() {
        $http({
            url: API_URL + 'get/today-purchases',
            method: 'GET'
        }).then( function success(response) {
            $scope.todayPurchasedLicenses = response.data.data;
        }, function error(response) {

        });
    }

    $scope.cmPurchased = function() {
        $http({
            url: API_URL + 'get/product-based/license-summary',
            method: 'GET'
        }).then( function success(response) {
            $scope.cmPurchasedLicenses = response.data.data;
        }, function error(response) {

        });
    }

    $scope.summary();
    $scope.todayPurchases();
    $scope.cmPurchased();
});


/******** Product Controller ********/

app.controller('productController', function($scope, $http, $document, $timeout, API_URL) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
        //$scope.filterTriggerText = !$scope.filtersToggle;
    };

    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };

    $scope.perPage = 10;
    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    $scope.getPaginateData = function(pageNumber) {
        $scope.nameFilter = ($scope.nameFilter) ? $scope.nameFilter : '';
        $scope.codeFilter = ($scope.codeFilter) ? $scope.codeFilter : '';
        $scope.productIdFilter = ($scope.productIdFilter) ? $scope.productIdFilter : '';
        $scope.durationFilter = ($scope.durationFilter) ? $scope.durationFilter : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.loading = true;

        if(pageNumber === undefined) {
            pageNumber = '1';
        }

        var filters = 'product_name=' + $scope.nameFilter + '&product_code=' + $scope.codeFilter + '&product_id=' + $scope.productIdFilter + '&status=' + $scope.statusFilter;

        $http({
            url: API_URL + 'get/products?' + filters + '&page=' + pageNumber + '&per_page=' + $scope.perPage,
            method: 'GET',
        }).then( function success(response) {
            var responseData = response.data.data;
            $scope.products = responseData.data;
            $scope.dataFrom = responseData.from;
            $scope.dataTo = responseData.to;
            $scope.totalData = responseData.total;
            $scope.totalPages   = responseData.last_page;
            $scope.currentPage  = responseData.current_page;
            $scope.lastPage  = responseData.last_page;
            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
        });
    };

    $scope.getProductBasedActiveLicenseCount = function() {
        $http({
            url: API_URL + 'get/product-based/license-count?status=PURCHASED',
            method: 'GET',
        }).then( function(response) {
            $scope.activeLicenseCount = response.data.data;
        });
    };

    $scope.getProductBasedLicenseCount = function() {
        $http({
            url: API_URL + 'get/product-based/license-count',
            method: 'GET',
        }).then( function(response) {
            $scope.licenseCount = response.data.data;
        });
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData(pageNumber);
    $scope.getProductBasedLicenseCount();
    $scope.getProductBasedActiveLicenseCount();

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };

    $scope.getProductData = function(product) {
        var element = angular.element($document[0].getElementById('updateProduct-error-res'));
        element.html('');

        $scope.updateProductName = product.product_name;
        $scope.updateProductCode = product.product_code;
        $scope.updateProductPrefix = product.product_prefix;
        var description = angular.fromJson(product.description);
        $scope.updateDescription = description[0].Content.join(' ');
        $scope.updateStatus = product.status;
        $scope.productId = product.product_uuid;

        if ($scope.licenseCount[product.product_code] > 0) {
            $scope.isPrefixReadOnly = true;
        }
        else {
            $scope.isPrefixReadOnly = false;
        }
    };

    $scope.generateProductCode = function(productName) {
        $scope.productCode = productName.replace(/\s+/g, '_').toUpperCase();
    };

    $scope.generateProductPrefix = function(productPrefix) {
        $scope.productPrefix = productPrefix.replace(/^[^\w]|[^\w-&()\s*]/g, '').replace(/\s+/g, '_').toUpperCase();
    };

    $scope.generateUpdateProductPrefix = function(productPrefix) {
        $scope.updateProductPrefix = productPrefix.replace(/^[^\w]|[^\w-&()\s*]/g, '').replace(/\s+/g, '_').toUpperCase();
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'addProductForm')
        {
            $scope.addProductForm.$touched = false;
            $scope.addProductForm.$untouched = true;
            $scope.addProductForm.$dirty = false;
            $scope.addProductForm.$pristine = true;

            $scope.addProductForm.productName.$touched = false;
            $scope.addProductForm.productName.$untouched = true;
            $scope.addProductForm.productName.$dirty = false;
            $scope.addProductForm.productName.$pristine = true;

            $scope.addProductForm.productCode.$touched = false;
            $scope.addProductForm.productCode.$untouched = true;
            $scope.addProductForm.productCode.$dirty = false;
            $scope.addProductForm.productCode.$pristine = true;

            $scope.addProductForm.productPrefix.$touched = false;
            $scope.addProductForm.productPrefix.$untouched = true;
            $scope.addProductForm.productPrefix.$dirty = false;
            $scope.addProductForm.productPrefix.$pristine = true;

            $scope.addProductForm.description.$touched = false;
            $scope.addProductForm.description.$untouched = true;
            $scope.addProductForm.description.$dirty = false;
            $scope.addProductForm.description.$pristine = true;

            $scope.addProductForm.status.$touched = false;
            $scope.addProductForm.status.$untouched = true;
            $scope.addProductForm.status.$dirty = false;
            $scope.addProductForm.status.$pristine = true;

            $scope.productName = '';
            $scope.productCode = '';
            $scope.productPrefix = '';
            $scope.description = '';
            $scope.status = '';
        }

        if (formName == 'importProductForm')
        {
            $scope.importProductForm.$touched = false;
            $scope.importProductForm.$untouched = true;
            $scope.importProductForm.$dirty = false;
            $scope.importProductForm.$pristine = true;

            $scope.importProductForm.importFile.$touched = false;
            $scope.importProductForm.importFile.$untouched = true;
            $scope.importProductForm.importFile.$dirty = false;
            $scope.importProductForm.importFile.$pristine = true;

            $scope.importFile = '';
            angular.element($document[0].getElementById('importFile')).val('');
        }
    };

    $scope.getProductId = function(product, columnId, alertType) {
        $scope.productId = product.product_uuid;
        $scope.alertType = alertType;
    };

    $scope.addProduct = function() {
        var element = angular.element($document[0].getElementById('addProduct-error-res'));
        element.html('');

        $http({
            url: API_URL + 'add/product',
            method: 'POST',
            data: {
                product_name: $scope.productName,
                product_code: $scope.productCode,
                product_prefix: $scope.productPrefix,
                description: $scope.description,
                status: $scope.status
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#addModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'Product has been <b>added</b> successfully.');

            let responseData = response.data.data;
            $scope.products.unshift(responseData);
            $scope.totalData += 1;
            $scope.dataTo += 1;

        }, function error(response) {
            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#addModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('addProduct-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    }

    $scope.updateProduct = function() {
        $http({
            url: API_URL + 'update/product',
            method: 'POST',
            data: {
                id: $scope.productId,
                product_name: $scope.updateProductName,
                product_prefix: $scope.updateProductPrefix,
                description: $scope.updateDescription,
                status: $scope.updateStatus,
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#updateModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'Product has been <b>updated</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.products.findIndex(x => x.product_uuid == responseData.product_uuid);

            if (index > -1)
            {
                $scope.products[index] = responseData;
            }
        }, function error(response) {
            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#updateModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('updateProduct-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    }

    $scope.importProduct = function() {
        var element = angular.element($document[0].getElementById('importProduct-error-res'));
        element.html('');

        let importedFile = angular.element($document[0].getElementById('importFile'));
        let payload = new FormData();
        payload.append('importedFile', importedFile[0].files[0]);
        console.log(importedFile[0].files[0]);

        $http({
            url: API_URL + 'import/product',
            method: 'POST',
            dataType: 'JSON',
            cache: false,
            processData: false,
            headers: {'Content-Type': undefined },
            transformRequest: angular.identity,
            data: payload
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#importModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'Product has been <b>added</b> successfully.');

            let responseData = response.data.data;
            $scope.products.unshift(responseData);
            $scope.totalData += 1;
            $scope.dataTo += 1;

        }, function error(response) {
            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#importModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('importProduct-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    }

    $scope.exportProduct = function() {
        $scope.loading = true;

        $http({
            url: API_URL + 'export/product',
            method: 'GET',
            responseType: 'arraybuffer'
        }).then(function success(response) {
            var headers = response.headers();
            var data = response.data;

            var filename = headers['x-filename'] || headers['content-disposition'].replace('attachment; filename=', '');
            var contentType = headers['content-type'];

            var linkElement = document.createElement('a');
            try {
                var blob = new Blob([data], { type: contentType });
                var url = window.URL.createObjectURL(blob);

                linkElement.setAttribute('href', url);
                linkElement.setAttribute("download", filename);

                var clickEvent = new MouseEvent("click", {
                    "view": window,
                    "bubbles": true,
                    "cancelable": false
                });
                linkElement.dispatchEvent(clickEvent);
                $scope.loading = false;
            } catch (ex) {
                console.log(ex);
                $scope.loading = false;
            }

        }, function error(response) {
            console.log(response);
            $scope.loading = false;
        });
    }

    $scope.syncProduct = function(id) {
        $scope.spinnerLoading = true;

        $http({
            url: API_URL + 'wp/product/sync',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.spinnerLoading = false;

            // Show the notification
            notificationAlert('Success', 'Product has been <b>updated</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.products.findIndex(x => x.product_uuid == responseData.product_uuid);
            if (index > -1)
            {
                $scope.products[index] = responseData;
            }

        }, function error(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.spinnerLoading = false;

            // Show the notification
            notificationAlert('Failure', 'There is no recent updates!');
        });
    };

    $scope.deleteProduct = function(id) {
        $scope.spinnerLoading = true;

        $http({
            url: API_URL + 'delete/product',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.spinnerLoading = false;

            // Show the notification
            notificationAlert('Success', 'Product has been <b>deleted</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.products.findIndex(x => x.product_uuid == responseData.product_uuid);

            if (index > -1)
            {
                $scope.products.splice(index, 1);
                $scope.totalData -= 1;
                $scope.dataTo -= 1;
            }
        });
    };
});


/******** Package Controller ********/

app.controller('packageController', function($scope, $http, $document, $timeout, $filter, $compile, API_URL) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
        //$scope.filterTriggerText = !$scope.filtersToggle;
    };

    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };

    $scope.perPage = 10;

    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    $scope.getPaginateData = function(pageNumber) {
        $scope.nameFilter = ($scope.nameFilter) ? $scope.nameFilter : '';
        $scope.codeFilter = ($scope.codeFilter) ? $scope.codeFilter : '';
        $scope.productCodesFilter = ($scope.productCodesFilter) ? $scope.productCodesFilter.replace(/ /g, '_').toUpperCase() : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.loading = true;

        if(pageNumber === undefined) {
            pageNumber = '1';
        }

        var filters = 'package_name=' + $scope.nameFilter + '&package_code=' + $scope.codeFilter + '&product_codes=' + $scope.productCodesFilter + '&status=' + $scope.statusFilter;

        $http({
            url: API_URL + 'get/packages?' + filters + '&page=' + pageNumber + '&per_page=' + $scope.perPage,
            method: 'GET',
        }).then( function success(response) {
            var responseData = response.data.data;
            $scope.packages = responseData.data;
            $scope.dataFrom = responseData.from;
            $scope.dataTo = responseData.to;
            $scope.totalData = responseData.total;
            $scope.totalPages   = responseData.last_page;
            $scope.currentPage  = responseData.current_page;
            $scope.lastPage  = responseData.last_page;
            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
        });
    };

    $scope.productCodes = [];
    $scope.selectedProductCodes = [];
    $scope.productCodesDropdownSetting = {
        scrollable: true,
        scrollableHeight : '200px',
        styleActive: true,
        checkBoxes: true,
        showUncheckAll: false,
        searchField: 'product_name',
        enableSearch: true,
        idProperty: 'product_code',
        template: '[[ option.product_name ]]',
        smartButtonMaxItems: 3,
        smartButtonTextProvider(selectionArray) { return selectionArray.length + ' items selected'; }
    };
    $scope.productCodesDropdownText = {
        checkAll: 'Select All',
        uncheckAll: 'Unselect All',
    };

    //Get Products Codes
    $scope.getProducts = function() {
        $http({
            url: API_URL + 'get/products?status=ACTIVE&page=all&sort_by=product_name&sort_order=ASC',
            method: 'GET',
        }).then( function success(response) {
            $scope.productData = response.data.data.data;
            $scope.productCodes.push($scope.productData);
        });
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData(pageNumber);
    $scope.getProducts();

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };

    $scope.getPackageData = function(package) {
        var element = angular.element($document[0].getElementById('updatePackage-error-res'));
        element.html('');

        $scope.updateSelectedProductCodes = [];
        var productCodes = angular.fromJson(package.product_codes), productName = '', productCodesObj = {};
        angular.forEach($scope.productData, function(product, index) {
            angular.forEach(productCodes, function(product_code, index) {
                if (product_code == product.product_code)
                {
                    $scope.updateSelectedProductCodes.push({'product_name': product.product_name, 'product_code': product.product_code});
                }
            });
        });

        $scope.updatePackageName = package.package_name;
        $scope.updatePackageCode = package.package_code;
        $scope.updateExclusivePackage = package.exclusive_package;
        $scope.updateStatus = package.status;
        $scope.packageId = package.package_uuid;
    };

    $scope.generatePackageCode = function(productName) {
        if ($scope.packageName != '' && $scope.packageName != undefined)
        {
            $scope.packageCode = $scope.packageName.replace(/ /g, '_').toUpperCase();
        }
    };

    $scope.displayShortProductNames = function(productCodes, counter) {
        var productCodes = angular.fromJson(productCodes), productName = '', fullProductNames = '';
        productCodes.sort();
        var breakLoop = false, showMore = false;

        angular.forEach(productCodes, function(product_code, key) {
            angular.forEach($scope.productData, function(product, index) {
                if (product_code == product.product_code)
                {
                    fullProductNames += product.product_name + ', ';
                    if (key == 3 && productCodes.length > 3 )
                    {
                        productName = productName.slice(0, -2) + ' <span data-ng-click="displayProductNames(true, ' + counter + ')" class="text-info text-decoration-underline cursor-pointer">Show More</span>, ';
                        breakLoop = true;
                    }
                    else if ((key == (productCodes.length - 1)) && !breakLoop)
                    {
                        if (productCodes.length < 4)
                        {
                            if (product.product_name.length >= 10)
                            {
                                productName += product.product_name.substring(0, 10) + "..." + ', ';
                                showMore = true;
                            }
                            else
                                productName +=  product.product_name + ', ';
                        }
                        if (showMore)
                        {
                            productName = productName.slice(0, -2) + ' <span data-ng-click="displayProductNames(true, ' + counter + ')" class="text-info text-decoration-underline cursor-pointer">Show More</span>, ';
                        }
                        breakLoop = true;

                    }
                    else if (!breakLoop)
                    {
                        if (product.product_name.length >= 10)
                        {
                            productName += product.product_name.substring(0, 10) + "..." + ', ';
                            showMore = true;
                        }
                        else
                            productName += product.product_name + ', ';
                    }
                }
            });
        });
        return '<div id="full-product-names-' + counter + '" class="d-none">' + fullProductNames.slice(0, -2) + ' <span data-ng-click="displayProductNames(false, ' + counter + ')" class="text-info text-decoration-underline cursor-pointer">Show Less</span>' + '</div>' + '<div id="partial-product-names-' + counter + '">' + productName.slice(0, -2) + '</div>';
    };

    $scope.displayProductNames = function(type, counter) {
        var fullProduct = angular.element($document[0].getElementById('full-product-names-' + counter));
        var partialProduct = angular.element($document[0].getElementById('partial-product-names-' + counter));

        if (type)
        {
            fullProduct.removeClass('d-none');
            partialProduct.addClass('d-none');
        }
        else
        {
            fullProduct.addClass('d-none');
            partialProduct.removeClass('d-none');
        }
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'addPackageForm')
        {
            $scope.addPackageForm.$touched = false;
            $scope.addPackageForm.$untouched = true;
            $scope.addPackageForm.$dirty = false;
            $scope.addPackageForm.$pristine = true;

            $scope.addPackageForm.packageName.$touched = false;
            $scope.addPackageForm.packageName.$untouched = true;
            $scope.addPackageForm.packageName.$dirty = false;
            $scope.addPackageForm.packageName.$pristine = true;

            $scope.addPackageForm.packageCode.$touched = false;
            $scope.addPackageForm.packageCode.$untouched = true;
            $scope.addPackageForm.packageCode.$dirty = false;
            $scope.addPackageForm.packageCode.$pristine = true;

            $scope.addPackageForm.status.$touched = false;
            $scope.addPackageForm.status.$untouched = true;
            $scope.addPackageForm.status.$dirty = false;
            $scope.addPackageForm.status.$pristine = true;

            $scope.packageName = '';
            $scope.packageCode = '';
            $scope.exclusivePackage = '';
            $scope.selectedProductCodes = [];
            $scope.status = '';
        }
    };

    $scope.addPackage = function() {
        var element = angular.element($document[0].getElementById('addPackage-error-res'));
        element.html('');

        var productCodes = [];
        angular.forEach($scope.selectedProductCodes, function(product, index) {
            productCodes[index] = product.product_code;
        });

        $http({
            url: API_URL + 'add/package',
            method: 'POST',
            data: {
                package_name: $scope.packageName,
                package_code: $scope.packageCode,
                product_codes: productCodes,
                exclusive_package: $scope.exclusivePackage,
                status: $scope.status
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#addModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'Package has been <b>added</b> successfully.');

            let responseData = response.data.data;
            $scope.packages.unshift(responseData);
            $scope.totalData += 1;
            $scope.dataTo += 1;

        }, function error(response) {
            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#addModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('addPackage-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    }

    $scope.updatePackage = function() {
        var element = angular.element($document[0].getElementById('updatePackage-error-res'));
        element.html('');

        var productCodes = [];
        angular.forEach($scope.updateSelectedProductCodes, function(product, index) {
            productCodes[index] = product.product_code;
        });

        $http({
            url: API_URL + 'update/package',
            method: 'POST',
            data: {
                id: $scope.packageId,
                package_name: $scope.updatePackageName,
                package_code: $scope.updatePackageCode,
                product_codes: productCodes,
                exclusive_package: $scope.updateExclusivePackage,
                status: $scope.updateStatus,
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#updateModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'Package has been <b>updated</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.packages.findIndex(x => x.package_uuid == responseData.package_uuid);

            if (index > -1)
            {
                $scope.packages[index] = responseData;
            }
        }, function error(response) {
            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#updateModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('updatePackage-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    }
});


/******** License Type Controller ********/

app.controller('licenseTypeController', function($scope, $http, $document, $timeout, $filter, API_URL) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
    };


    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };

    $scope.perPage = 10;
    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    /****  Get licenseTypes ****/
    $scope.getPaginateData = function(pageNumber) {
        $scope.nameFilter = ($scope.nameFilter) ? $scope.nameFilter : '';
        $scope.codeFilter = ($scope.codeFilter) ? $scope.codeFilter : '';
        $scope.durationFilter = ($scope.durationFilter) ? $scope.durationFilter : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.loading = true;

        if(pageNumber === undefined){
            pageNumber = '1';
        }

        var filters = 'name=' + $scope.nameFilter + '&code=' + $scope.codeFilter + '&expiry_duration=' + $scope.durationFilter + '&status=' + $scope.statusFilter;

        $http({
            url: API_URL + 'get/licenseTypes?' + filters + '&page=' + pageNumber + '&per_page=' + $scope.perPage,
            method: 'GET'
        }).then( function success(response) {
            var responseData = response.data.data;
            $scope.licenseTypes = responseData.data;
            $scope.dataFrom = responseData.from;
            $scope.dataTo = responseData.to;
            $scope.totalData = responseData.total;
            $scope.totalPages   = responseData.last_page;
            $scope.currentPage  = responseData.current_page;
            $scope.lastPage  = responseData.last_page;
            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
        });
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData(pageNumber);

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };

    $scope.generateLicenseCode = function(licenseTypename) {
        $scope.licenseCode = licenseTypename.replace(/ /g, '_').toUpperCase();
    };

    $scope.getTodayDate = function() {
        let currentDate = new Date();

        let date = currentDate.getDate().toString();
        date = (date.length > 1) ? date : '0' + date;

        let month = '0' + (currentDate.getMonth() + 1).toString();
        month = (month.length > 1) ? month : '0' + month;

        let year = currentDate.getFullYear().toString();
        let today = year + '-' + month + '-' + date;

        $scope.todayDate = today.toString();
    };

    $scope.expiryDurationFormat = function(expiryPeriod) {
        $scope.expiryDurationDate = '';
        $scope.expiryDuration = '';
        $scope.updateExpiryDurationDate = '';
        $scope.updateExpiryDuration = '';

        if (expiryPeriod == 'Date')
        {
            $scope.expiryDurationDateFlag = true;
        }
        else
        {
            $scope.expiryDurationDateFlag = false;
        }
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'addLicenseTypeForm')
        {
            $scope.addLicenseTypeForm.$touched = false;
            $scope.addLicenseTypeForm.$untouched = true;
            $scope.addLicenseTypeForm.$dirty = false;
            $scope.addLicenseTypeForm.$pristine = true;

            $scope.addLicenseTypeForm.licenseTypename.$touched = false;
            $scope.addLicenseTypeForm.licenseTypename.$untouched = true;
            $scope.addLicenseTypeForm.licenseTypename.$dirty = false;
            $scope.addLicenseTypeForm.licenseTypename.$pristine = true;

            $scope.addLicenseTypeForm.licenseCode.$touched = false;
            $scope.addLicenseTypeForm.licenseCode.$untouched = true;
            $scope.addLicenseTypeForm.licenseCode.$dirty = false;
            $scope.addLicenseTypeForm.licenseCode.$pristine = true;

            $scope.addLicenseTypeForm.expiryPeriod.$touched = false;
            $scope.addLicenseTypeForm.expiryPeriod.$untouched = true;
            $scope.addLicenseTypeForm.expiryPeriod.$dirty = false;
            $scope.addLicenseTypeForm.expiryPeriod.$pristine = true;

            $scope.addLicenseTypeForm.expiryDuration.$touched = false;
            $scope.addLicenseTypeForm.expiryDuration.$untouched = true;
            $scope.addLicenseTypeForm.expiryDuration.$dirty = false;
            $scope.addLicenseTypeForm.expiryDuration.$pristine = true;

            $scope.addLicenseTypeForm.description.$touched = false;
            $scope.addLicenseTypeForm.description.$untouched = true;
            $scope.addLicenseTypeForm.description.$dirty = false;
            $scope.addLicenseTypeForm.description.$pristine = true;

            $scope.addLicenseTypeForm.status.$touched = false;
            $scope.addLicenseTypeForm.status.$untouched = true;
            $scope.addLicenseTypeForm.status.$dirty = false;
            $scope.addLicenseTypeForm.status.$pristine = true;

            $scope.licenseTypename = '';
            $scope.licenseCode = '';
            $scope.expiryPeriod = '';
            $scope.expiryDuration = '';
            $scope.expiryDurationDate = '';
            $scope.status = '';
        }
    };

    /**** Add LicenseType *****/
    $scope.addLicenseType = function() {
        $scope.spinnerLoading = true;

        $http({
            url: API_URL + 'add/licenseType',
            method: 'POST',
            data: {
                name: $scope.licenseTypename,
                code: $scope.licenseCode,
                expiry_period: $scope.expiryPeriod,
                expiry_duration: $scope.expiryDuration,
                expiry_duration_date: $scope.expiryDurationDate,
                description: $scope.description,
                status: $scope.status
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#addLicenseTypeModal .btn-close').click();
            $scope.spinnerLoading = false;

            // Show the notification
            notificationAlert('Success', 'License type has been <b>added</b> successfully.');

            let responseData = response.data.data;
            $scope.licenseTypes.unshift(responseData);
            $scope.totalData += 1;
            $scope.dataTo += 1;

        }, function error(response) {
            $scope.spinnerLoading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#addLicenseTypeModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('addLicenseType-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    };

    $scope.getLicenseType = function(licenseType) {
        var element = angular.element($document[0].getElementById('updateLicenseType-error-res'));
        element.html('');

        $scope.updateLicenseTypename = licenseType.name;
        $scope.updateLicenseCode = licenseType.code;
        $scope.updateDescription = licenseType.description;
        $scope.updateStatus = licenseType.status;
        $scope.licenseTypeId = licenseType.id;

        if (licenseType.duration_type == 'DURATION')
        {
            $scope.updateExpiryDuration = Number(licenseType.expiry_duration.split(' ')[0]);
            $scope.updateExpiryPeriod = licenseType.expiry_duration.split(' ')[1];
        }
        else if (licenseType.duration_type == 'DATE')
        {
            $scope.updateExpiryDurationDate = $filter('date')(licenseType.expiry_duration, 'dd-MM-yyyy');;
            $scope.updateExpiryPeriod = 'Date';
        }

        if ($scope.updateExpiryPeriod == 'Date')
        {
            $scope.expiryDurationDateFlag = true;
        }
        else
        {
            $scope.expiryDurationDateFlag = false;
        }
    };

    $scope.updateLicenseType = function() {
        $scope.spinnerLoading = true;

        $http({
            url: API_URL + 'update/licenseType',
            method: 'POST',
            data: {
                id: $scope.licenseTypeId,
                name: $scope.updateLicenseTypename,
                code: $scope.updateLicenseCode,
                expiry_period: $scope.updateExpiryPeriod,
                expiry_duration: $scope.updateExpiryDuration,
                expiry_duration_date: $scope.updateExpiryDurationDate,
                description: $scope.updateDescription,
                status: $scope.updateStatus,
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#updateModal .btn-close').click();
            $scope.spinnerLoading = false;

            // Show the notification
            notificationAlert('Success', 'License type has been <b>updated</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.licenseTypes.findIndex(x => x.id == responseData.id);

            if (index > -1)
            {
                $scope.licenseTypes[index] = responseData;
            }
        }, function error(response) {
            $scope.spinnerLoading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#updateModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('updateLicenseType-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    }

    $scope.importLicenseType = function() {
        var element = angular.element($document[0].getElementById('importLicenseType-error-res'));
        element.html('');

        let importedFile = angular.element($document[0].getElementById('importFile'));
        let payload = new FormData();
        payload.append('importedFile', importedFile[0].files[0]);
        console.log(importedFile[0].files[0]);

        $http({
            url: API_URL + 'import/licenseType',
            method: 'POST',
            dataType: 'JSON',
            cache: false,
            processData: false,
            headers: {'Content-Type': undefined },
            transformRequest: angular.identity,
            data: payload
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#importModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License type has been <b>added</b> successfully.');

            let responseData = response.data.data;
            $scope.products.unshift(responseData);
            $scope.totalData += 1;
            $scope.dataTo += 1;

        }, function error(response) {
            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#importModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('importLicenseType-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    }

    $scope.exportLicenseType = function() {
        $scope.loading = true;

        $http({
            url: API_URL + 'export/licenseType',
            method: 'GET',
            responseType: 'arraybuffer'
        }).then(function success(response) {
            var headers = response.headers();
            var data = response.data;

            var filename = headers['x-filename'] || headers['content-disposition'].replace('attachment; filename=', '');
            var contentType = headers['content-type'];

            var linkElement = document.createElement('a');
            try {
                var blob = new Blob([data], { type: contentType });
                var url = window.URL.createObjectURL(blob);

                linkElement.setAttribute('href', url);
                linkElement.setAttribute("download", filename);

                var clickEvent = new MouseEvent("click", {
                    "view": window,
                    "bubbles": true,
                    "cancelable": false
                });
                linkElement.dispatchEvent(clickEvent);
                $scope.loading = false;
            } catch (ex) {
                console.log(ex);
                $scope.loading = false;
            }

        }, function error(response) {
            console.log(response);
            $scope.loading = false;
        });
    };

    $scope.deleteLicenseType = function(id) {
        $scope.spinnerLoading = true;
        $http({
            url: API_URL + 'delete/licenseType',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.spinnerLoading = false;

            // Show the notification
            notificationAlert('Success', 'License type has been <b>deleted</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.licenseTypes.findIndex(x => x.id == responseData.id);

            if (index > -1)
            {
                $scope.licenseTypes.splice(index, 1);
                $scope.totalData -= 1;
                $scope.dataTo -= 1;
            }
        }, function error(response) {
            $scope.spinnerLoading = false;
        });
    };
});


app.controller('licenseController', function($scope, $http, $document, $timeout, API_URL, $location) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];
    $scope.counts = 1;

    // For get form data from ng-if content
    $scope.form = {};

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
    };

    $scope.globalFiltersToggle = false;
    $scope.globaltogglefilters = function() {
        $scope.globalFiltersToggle = !$scope.globalFiltersToggle;

        if (!$scope.globalFiltersToggle)
        {
            $scope.licenseGlobalSearch = '';
            $scope.getPaginateData();
        }
    };

    //Generate License
    $scope.generateLicense = function() {
        var element = angular.element($document[0].getElementById('generateLicense-error-res'));
        element.html('');

        $http({
            url: API_URL + 'license/generate',
            method: 'POST',
            data: {
                product_code: angular.fromJson($scope.productCode),
                license_code: $scope.licenseCode,
                counts: $scope.counts,
                order_source: $scope.orderSource,
                order_reference_no: $scope.orderReferenceNo,
                order_info: $scope.orderInfo,
                order_time: $scope.orderTime,
                email: $scope.email,
                first_name: $scope.firstName,
                last_name: $scope.lastName,
                phone_no: $scope.phoneNo
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#generateLicenseModal .btn-close').click();

            var responseData = response.data.data;
            var generatedLicenseKeyArray = [];
            var generatedLicenseKeyText = '';

            angular.forEach(responseData, function (value, key) {
                $scope.licenselist.unshift(value);
                $scope.totalData += 1;
                $scope.dataTo += 1;

                if (generatedLicenseKeyArray.indexOf(value.license_key) == -1)
                {
                    generatedLicenseKeyArray[key] = value.license_key;
                }
            });

            angular.forEach(generatedLicenseKeyArray, function(value, key) {
                generatedLicenseKeyText += '<div>' + value + '</div>';
            });
            angular.element($document[0].querySelector('#generatedLicenseKeys')).html(generatedLicenseKeyText);
            //$scope.generatedLicenseKeys = generatedLicenseKeyText.trim();
            document.querySelector('#generatedLicenseKeysModalBtn').click();

        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#generateLicenseModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('generateLicense-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    };

    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };

    //Get licenses
    $scope.getPaginateData = function(pageNumber) {
        $scope.licenseFilter = ($scope.licenseFilter) ? $scope.licenseFilter : '';
        $scope.licenseTypeFilter = ($scope.licenseTypeFilter) ? $scope.licenseTypeFilter : '';
        $scope.productNameFilter = ($scope.productNameFilter) ? $scope.productNameFilter : '';
        $scope.macAddressFilter = ($scope.macAddressFilter) ? $scope.macAddressFilter : '';
        $scope.orderReferenceNoFilter = ($scope.orderReferenceNoFilter) ? $scope.orderReferenceNoFilter : '';
        $scope.orderSourceFilter = ($scope.orderSourceFilter) ? $scope.orderSourceFilter : '';
        $scope.emailFilter = ($scope.emailFilter) ? $scope.emailFilter : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.purchaseFromDateFilter = ($scope.purchaseFromDateFilter) ? $scope.purchaseFromDateFilter : '';
        $scope.purchaseToDateFilter = ($scope.purchaseToDateFilter) ? $scope.purchaseToDateFilter : '';
        $scope.expiryFromDateFilter = ($scope.expiryFromDateFilter) ? $scope.expiryFromDateFilter : '';
        $scope.expiryToDateFilter = ($scope.expiryToDateFilter) ? $scope.expiryToDateFilter : '';
        $scope.licenseGlobalSearch = ($scope.licenseGlobalSearch) ? $scope.licenseGlobalSearch : '';
        $scope.loading = true;

        if(pageNumber === undefined){
            pageNumber = '1';
        }

        var filters = 'search=' + $scope.licenseGlobalSearch + '&license_key=' + $scope.licenseFilter + '&license_type=' + $scope.licenseTypeFilter + '&product_name=' + $scope.productNameFilter + '&mac_address=' + $scope.macAddressFilter + '&order_reference_no=' + $scope.orderReferenceNoFilter + '&order_source=' + $scope.orderSourceFilter + '&email=' + $scope.emailFilter + '&status=' + $scope.statusFilter + '&purchased_date_from=' + $scope.purchaseFromDateFilter + '&purchased_date_to=' + $scope.purchaseToDateFilter + '&expiry_from_date=' + $scope.expiryFromDateFilter + '&expiry_to_date=' + $scope.expiryToDateFilter;

        $http({
            url: API_URL + 'get/licenses?' + filters + '&page=' + pageNumber,
            method: 'GET',
        }).then( function success(response) {
            var responseData =  response.data.data;
            $scope.licenselist = responseData.data;
            $scope.dataFrom = responseData.from;
            $scope.dataTo = responseData.to;
            $scope.totalData = responseData.total;
            $scope.totalPages = responseData.last_page;
            $scope.currentPage = responseData.current_page;
            $scope.lastPage  = responseData.last_page;
            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
        });
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData();

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };


    //Get Products Codes
    $scope.getProducts = function() {
        $http({
            url: API_URL + 'get/products?status=ACTIVE&page=all&sort_by=product_name&sort_order=ASC',
            method: 'GET',
        }).then( function success(response) {
            $scope.productCodes = response.data.data.data;
        });
    };

    //Get License Codes
    $scope.getLicenseCodes = function() {
        $http({
            url: API_URL + 'get/licenseTypes?status=AVAILABLE&page=all&sort_by=name&sort_order=ASC',
            method: 'GET',
        }).then( function success(response) {
            $scope.licenseCodes = response.data.data.data;
        });
    };

    //Get Packages
    $scope.getPackages = function() {
        $http({
        url: API_URL + 'get/packages?status=AVAILABLE&page=all&sort_by=package_name&sort_order=ASC',
        method: 'GET',
        }).then( function success(response) {
            $scope.packages = response.data.data.data;
        });
    };

    $scope.getCodes = function() {
        $scope.getPackages();
        $scope.getProducts();
        $scope.getLicenseCodes();
    };

    //Get License and Customer Details
    $scope.getlicenseDetails = function(id) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/license-details',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            $scope.licenseDetail = response.data.data;
            $scope.loading = false;
        }, function error(response) {
            $scope.loading = false;
        });
    };

    $scope.getYesterdayDate = function() {
        let currentDate = new Date();
        currentDate.setDate(currentDate.getDate() - 1);

        let date = currentDate.getDate().toString();
        date = (date.length > 1) ? date : '0' + date;

        let month = '0' + (currentDate.getMonth() + 1).toString();
        month = (month.length > 1) ? month : '0' + month;

        let year = currentDate.getFullYear().toString();
        let yesterday = year + '-' + month + '-' + date;

        $scope.yesterdayDate = yesterday.toString();
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'showLicenseForm')
        {
            $scope.showLicenseForm.$touched = false;
            $scope.showLicenseForm.$untouched = true;
            $scope.showLicenseForm.$dirty = false;
            $scope.showLicenseForm.$pristine = true;

            $scope.showLicenseForm.password.$touched = false;
            $scope.showLicenseForm.password.$untouched = true;
            $scope.showLicenseForm.password.$dirty = false;
            $scope.showLicenseForm.password.$pristine = true;

            $scope.password = '';
        }

        else if (formName == 'deactivateLicenseForm')
        {
            $scope.deactivateLicenseForm.$touched = false;
            $scope.deactivateLicenseForm.$untouched = true;
            $scope.deactivateLicenseForm.$dirty = false;
            $scope.deactivateLicenseForm.$pristine = true;

            $scope.deactivateLicenseForm.deactivateType.$touched = false;
            $scope.deactivateLicenseForm.deactivateType.$untouched = true;
            $scope.deactivateLicenseForm.deactivateType.$dirty = false;
            $scope.deactivateLicenseForm.deactivateType.$pristine = true;

            $scope.deactivateType = '';
        }

        else if (formName == 'activateLicenseForm')
        {
            $scope.activateLicenseForm.$touched = false;
            $scope.activateLicenseForm.$untouched = true;
            $scope.activateLicenseForm.$dirty = false;
            $scope.activateLicenseForm.$pristine = true;

            $scope.activateLicenseForm.activateType.$touched = false;
            $scope.activateLicenseForm.activateType.$untouched = true;
            $scope.activateLicenseForm.activateType.$dirty = false;
            $scope.activateLicenseForm.activateType.$pristine = true;

            $scope.activateType = '';
        }

        else if (formName == 'deleteLicenseForm')
        {
            $scope.deleteLicenseForm.$touched = false;
            $scope.deleteLicenseForm.$untouched = true;
            $scope.deleteLicenseForm.$dirty = false;
            $scope.deleteLicenseForm.$pristine = true;

            $scope.deleteLicenseForm.userPassword.$touched = false;
            $scope.deleteLicenseForm.userPassword.$untouched = true;
            $scope.deleteLicenseForm.userPassword.$dirty = false;
            $scope.deleteLicenseForm.userPassword.$pristine = true;

            $scope.userPassword = '';
            $scope.deleteType = '';
        }

        else if (formName == 'renewForm')
        {
            $scope.renewForm.$touched = false;
            $scope.renewForm.$untouched = true;
            $scope.renewForm.$dirty = false;
            $scope.renewForm.$pristine = true;

            $scope.renewForm.renewLicenseCode.$touched = false;
            $scope.renewForm.renewLicenseCode.$untouched = true;
            $scope.renewForm.renewLicenseCode.$dirty = false;
            $scope.renewForm.renewLicenseCode.$pristine = true;

            $scope.renewForm.renewalType.$touched = false;
            $scope.renewForm.renewalType.$untouched = true;
            $scope.renewForm.renewalType.$dirty = false;
            $scope.renewForm.renewalType.$pristine = true;

            $scope.renewLicenseCode = '';
            $scope.renewalType = '';
        }

        else if (formName == 'generateLicenseForm')
        {
            $scope.generateLicenseForm.$touched = false;
            $scope.generateLicenseForm.$untouched = true;
            $scope.generateLicenseForm.$dirty = false;
            $scope.generateLicenseForm.$pristine = true;

            $scope.generateLicenseForm.productCode.$touched = false;
            $scope.generateLicenseForm.productCode.$untouched = true;
            $scope.generateLicenseForm.productCode.$dirty = false;
            $scope.generateLicenseForm.productCode.$pristine = true;

            $scope.generateLicenseForm.licenseCode.$touched = false;
            $scope.generateLicenseForm.licenseCode.$untouched = true;
            $scope.generateLicenseForm.licenseCode.$dirty = false;
            $scope.generateLicenseForm.licenseCode.$pristine = true;

            $scope.generateLicenseForm.counts.$touched = false;
            $scope.generateLicenseForm.counts.$untouched = true;
            $scope.generateLicenseForm.counts.$dirty = false;
            $scope.generateLicenseForm.counts.$pristine = true;

            $scope.generateLicenseForm.orderSource.$touched = false;
            $scope.generateLicenseForm.orderSource.$untouched = true;
            $scope.generateLicenseForm.orderSource.$dirty = false;
            $scope.generateLicenseForm.orderSource.$pristine = true;

            $scope.generateLicenseForm.orderReferenceNo.$touched = false;
            $scope.generateLicenseForm.orderReferenceNo.$untouched = true;
            $scope.generateLicenseForm.orderReferenceNo.$dirty = false;
            $scope.generateLicenseForm.orderReferenceNo.$pristine = true;

            $scope.generateLicenseForm.orderInfo.$touched = false;
            $scope.generateLicenseForm.orderInfo.$untouched = true;
            $scope.generateLicenseForm.orderInfo.$dirty = false;
            $scope.generateLicenseForm.orderInfo.$pristine = true;

            $scope.generateLicenseForm.orderTime.$touched = false;
            $scope.generateLicenseForm.orderTime.$untouched = true;
            $scope.generateLicenseForm.orderTime.$dirty = false;
            $scope.generateLicenseForm.orderTime.$pristine = true;

            $scope.generateLicenseForm.email.$touched = false;
            $scope.generateLicenseForm.email.$untouched = true;
            $scope.generateLicenseForm.email.$dirty = false;
            $scope.generateLicenseForm.email.$pristine = true;

            $scope.generateLicenseForm.firstName.$touched = false;
            $scope.generateLicenseForm.firstName.$untouched = true;
            $scope.generateLicenseForm.firstName.$dirty = false;
            $scope.generateLicenseForm.firstName.$pristine = true;

            $scope.generateLicenseForm.lastName.$touched = false;
            $scope.generateLicenseForm.lastName.$untouched = true;
            $scope.generateLicenseForm.lastName.$dirty = false;
            $scope.generateLicenseForm.lastName.$pristine = true;

            $scope.generateLicenseForm.phoneNo.$touched = false;
            $scope.generateLicenseForm.phoneNo.$untouched = true;
            $scope.generateLicenseForm.phoneNo.$dirty = false;
            $scope.generateLicenseForm.phoneNo.$pristine = true;

            $scope.productCode = '';
            $scope.licenseCode = '';
            $scope.counts = 1;
            $scope.orderSource = '';
            $scope.orderReferenceNo = '';
            $scope.orderInfo = '';
            $scope.orderTime = '';
            $scope.email = '';
            $scope.firstName = '';
            $scope.lastName = '';
            $scope.phoneNo = '';
        }

        else if (formName == 'resetMacForm')
        {
            $scope.resetMacForm.$touched = false;
            $scope.resetMacForm.$untouched = true;
            $scope.resetMacForm.$dirty = false;
            $scope.resetMacForm.$pristine = true;

            $scope.resetMacForm.resetType.$touched = false;
            $scope.resetMacForm.resetType.$untouched = true;
            $scope.resetMacForm.resetType.$dirty = false;
            $scope.resetMacForm.resetType.$pristine = true;

            $scope.resetType = '';
        }
    };

    //Update License and Customer Details
    $scope.updateCustomerDetails = function() {

        $http({
            url: API_URL + 'license/customer-update',
            method: 'POST',
            data: {
                license_key: $scope.updateLicenseKey,
                mac_address: $scope.updateMacAddress,
                email: $scope.updateEmail,
                first_name: $scope.updateFirstName,
                last_name: $scope.updateLastName,
                phone_no: $scope.updatePhoneNo
            },
        }).then( function success(response) {
            window.location.href = window.location.href;
        }, function error(response) {
            var errors = response.data.data.error, errorData = '';
            var element = angular.element($document[0].getElementById('updateCustomer-error-res'));
            angular.forEach(errors, function (value, key) {
                errorData += '<div>* ' + value + '</div>';
            });
            element.html(errorData);
        });
    };

    $scope.userPasswordToggle = true;
    $scope.toggleUserPassword = function() {
        $scope.userPasswordToggle = !$scope.userPasswordToggle;
    };

    $scope.deleteLicenseKey = function() {
        var element = angular.element($document[0].getElementById('deleteLicenseKey-error-res'));
        element.text('');
        $scope.loading = true;

        $http({
            url: API_URL + 'delete/license',
            method: 'POST',
            data: {
                id : $scope.licenseId,
                password : $scope.userPassword,
                delete_type: ($scope.deleteType) ? $scope.deleteType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>deleted</b> successfully.');

            let responseData = response.data.data;

            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist.splice(index, 1);
                    $scope.totalData -= 1;
                    $scope.dataTo -= 1;
                }
            });

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('deleteLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    $scope.getLicenseHistory = function(id) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/license/history',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            $scope.licenseHistoryData = response.data.data;
            $scope.loading = false;
        }, function error(response) {

        });
    };

    // Sort By function
    $scope.sortByHistoryField = function(field) {
        $scope.sortByHistory = field;
        $scope.reverseHistory = !$scope.reverseHistory;
    };


    $scope.activateLicense = function() {
        $http({
            url: API_URL + 'license/activate',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                activate_type: ($scope.activateType) ? $scope.activateType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>activated</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });
        }, function error(response) {

        });
    };


    $scope.deactivateLicense = function() {
        console.log($scope.deactivateType);
        $http({
            url: API_URL + 'license/deactivate',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                deactivate_type: ($scope.deactivateType) ? $scope.deactivateType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>deactivated</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });

        }, function error(response) {

        });
    };

    /****  Show License Key Section ****/

    $scope.passwordToggle = true;
    $scope.togglePassword = function() {
        $scope.passwordToggle = !$scope.passwordToggle;
    };

    $scope.getLicenseId = function(license, columnId, alertType) {
        $scope.licenseId = license.license_uuid;
        $scope.licenseData = license;
        $scope.columnId = columnId;
        $scope.alertType = alertType;
        $scope.packageFlag = license.package;
        // console.log(license);
        if(alertType == 'ResetMACAlert'){
            $scope.clearFormData('resetMacForm');
            if(!license.package_id){
                $scope.isShowForm = false;
                $scope.resetType = 'PRODUCT';
                $scope.resetMacForm.resetType = 'PRODUCT';
            }
            // $scope.$apply();
            // $scope.$evalAsync();
            // console.log($scope.isShowForm);
        }
    };

    $scope.getRenewData = function(license, product) {
        $scope.renewLicenseId = license.license_uuid;
        $scope.renewLicense = license.hashed_license_key;
        $scope.renewProductCode = product.product_code;
        $scope.renewPackageFlag = license.package;
        $scope.renewLicenseCode = '';
        $scope.renewalType = '';
    };


    $scope.copyToClipboard = function (currentElement, targetId)
    {
        var r = document.createRange();
        r.selectNode(document.getElementById(targetId));
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(r);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
    };


    /*  // copyToClipboard Version 2
    $scope.copyToClipboard = function(currentElement, targetId) {
        angular.element(currentElement).attr('title', 'Copied');
        var targetElement = angular.element($document[0].getElementById(targetId));
        navigator.clipboard.writeText(targetElement.text());
    };
    */

    $scope.renewLicenseDetails = function() {
        var element = angular.element($document[0].getElementById('renewLicense-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'license/renewal',
            method: 'POST',
            data: {
                id: $scope.renewLicenseId,
                license_code: $scope.renewLicenseCode,
                renewal_type: ($scope.renewalType) ? $scope.renewalType : 'PRODUCT_CODE'
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#renewModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>renewed</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('renewLicense-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    $scope.getActualLicenseKey = function() {
        var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'get/actual-license',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                password: $scope.password
            }
        }).then( function success(response) {
            var responseData = response.data.data;
            var licenseColumn = angular.element($document[0].getElementById('LicenseKey-field-' + $scope.columnId));
            licenseColumn.text(responseData.license_key);

            angular.element($document[0].querySelector('#LicenseKey-field-' + $scope.columnId + ' ~ i.fa-eye')).remove();
            angular.element($document[0].querySelector('#LicenseKey-field-' + $scope.columnId + ' ~ i.fa-copy')).removeClass('d-none');

            /**** Close Modal ****/
            document.querySelector('#showLicenseKeyModal .btn-close').click();

            $scope.loading = false;

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };


    /****  Reset MAC Address ****/

    $scope.resetMACAddress = function() {
        $scope.loading = true;

        $http({
            url: API_URL + 'reset/mac-address',
            method: 'POST',
            data: {
                id: $scope.licenseId
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            document.querySelector('#licenseDetailModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'MAC Address has been <b>reset</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.licenselist.findIndex(x => x.license_uuid == responseData.license_uuid);

            if (index > -1)
            {
                $scope.licenselist[index] = responseData;
            }

        }, function error(response) {
            var error = response.data.data.error;
            $scope.loading = false;
        });
    };

     /****  Reset MAC Address V2 Api****/

     $scope.resetMAC = function() {
        $scope.loading = true;

        // console.log($scope.licenseId, $scope.resetType);
        // return;

        $http({
            url: API_URL + 'v2/reset/mac-address',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                type:$scope.resetType
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            document.querySelector('#licenseDetailModal .btn-close').click();
            $scope.loading = false;
            console.log(response);

            // Show the notification
            notificationAlert('Success', 'MAC Address has been <b>reset</b> successfully.');

            let responseData = response.data.data;

            responseData.forEach(responseItem => {
                const existingItemIndex = $scope.licenselist.findIndex(existingItem => existingItem.license_uuid === responseItem.license_uuid);                
                if (existingItemIndex !== -1) {
                  $scope.licenselist[existingItemIndex] = { ...$scope.licenselist[existingItemIndex], ...responseItem };
                }
              });

        }, function error(response) {
            // var error = response.data.data.error;
            // console.log(error);
            const { data : { error }, message} = response.data;
            $scope.loading = false;
            notificationAlert('Failure', error[0] || message);
        });
    };

});

app.controller('licenseControllerV1', function($scope, $http, $document, $timeout, API_URL, $location) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];
    $scope.counts = 1;
    $scope.expiryFromDateFilter = null;
    $scope.expiryToDateFilter = null;
    $scope.showDownload = false;
    // For get form data from ng-if content
    $scope.form = {};

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
    };

    $scope.globalFiltersToggle = false;
    $scope.globaltogglefilters = function() {
        $scope.globalFiltersToggle = !$scope.globalFiltersToggle;

        if (!$scope.globalFiltersToggle)
        {
            $scope.licenseGlobalSearch = '';
            $scope.getPaginateData();
        }
    };

    $scope.perPage = 10;
    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    // Tab switching logic
    $scope.activeTab = $location.search().type || 'PRODUCT';

    // Function to switch tabs
    $scope.switchTab = function(tab) {
      $scope.activeTab = tab;
      $location.search('type', tab);
      pageNumber = '1';
      $scope.getPaginateData(null);
    };

    //Generate License
    $scope.generateLicense = function() {
        var element = angular.element($document[0].getElementById('generateLicense-error-res'));
        element.html('');

        $http({
            url: API_URL + 'license/generate',
            method: 'POST',
            data: {
                product_code: angular.fromJson($scope.productCode),
                license_code: $scope.licenseCode,
                counts: $scope.counts,
                order_source: $scope.orderSource,
                order_reference_no: $scope.orderReferenceNo,
                order_info: $scope.orderInfo,
                order_time: $scope.orderTime,
                email: $scope.email,
                first_name: $scope.firstName,
                last_name: $scope.lastName,
                phone_no: $scope.phoneNo
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#generateLicenseModal .btn-close').click();

            var responseData = response.data.data;
            var generatedLicenseKeyArray = [];
            var generatedLicenseKeyText = '';

            angular.forEach(responseData, function (value, key) {
                // $scope.licenselist.unshift(value);
                // $scope.totalData += 1;
                // $scope.dataTo += 1;

                if (generatedLicenseKeyArray.indexOf(value.license_key) == -1)
                {
                    generatedLicenseKeyArray[key] = value.license_key;
                }
            });

            angular.forEach(generatedLicenseKeyArray, function(value, key) {
                generatedLicenseKeyText += '<div>' + value + '</div>';
            });
            angular.element($document[0].querySelector('#generatedLicenseKeys')).html(generatedLicenseKeyText);
            //$scope.generatedLicenseKeys = generatedLicenseKeyText.trim();
            document.querySelector('#generatedLicenseKeysModalBtn').click();
            $scope.getPaginateData();

        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#generateLicenseModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('generateLicense-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    };

    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };

    $scope.clearDateFilter = function() {
        $scope.expiryFromDateFilter = null;
        $scope.expiryToDateFilter = null;
        $scope.getPaginateData(1);
    }

    $scope.applyDateFilter = function(){

        if(!$scope.expiryFromDateFilter){
            notificationAlert('Failure', 'From date is Required');
            return;
        }
        if(!$scope.expiryToDateFilter){
            notificationAlert('Failure', 'To date is required');
            return;
        }
        if(new Date($scope.expiryFromDateFilter) > new Date($scope.expiryToDateFilter)){
            notificationAlert('Failure', 'From date should be less than to date');
            return;
        }
        $scope.showDownload= true;
        $scope.getPaginateData(1);
    }

    $scope.perPage = 10;
    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    $scope.downloadExcel = function() {
        let payload = {
            'expiry_from_date': $scope.expiryFromDateFilter,
            'expiry_to_date': $scope.expiryToDateFilter,
            'type': $scope.activeTab
        }
        $scope.loading = true;

        $http({
            url: API_URL + 'export/license/userwise',
            method: 'POST',
            data: payload,
            responseType: 'arraybuffer',
        }).then(function success(response) {
            var headers = response.headers();
            var data = response.data;

            var filename = headers['x-filename'] || headers['content-disposition'].replace('attachment; filename=', '');
            var contentType = headers['content-type'];

            var linkElement = document.createElement('a');
            try {
                var blob = new Blob([data], { type: contentType });
                var url = window.URL.createObjectURL(blob);

                linkElement.setAttribute('href', url);
                linkElement.setAttribute("download", filename);

                var clickEvent = new MouseEvent("click", {
                    "view": window,
                    "bubbles": true,
                    "cancelable": false
                });
                linkElement.dispatchEvent(clickEvent);
                $scope.loading = false;
            } catch (ex) {
                $scope.loading = false;
            }

        }, function error(response) {
            $scope.loading = false;
        });
    }

    //Get licenses
    $scope.getPaginateData = function(pageNumber) {
        $scope.licenseFilter = ($scope.licenseFilter) ? $scope.licenseFilter : '';
        $scope.licenseTypeFilter = ($scope.licenseTypeFilter) ? $scope.licenseTypeFilter : '';
        $scope.productNameFilter = ($scope.productNameFilter) ? $scope.productNameFilter : '';
        $scope.macAddressFilter = ($scope.macAddressFilter) ? $scope.macAddressFilter : '';
        $scope.orderReferenceNoFilter = ($scope.orderReferenceNoFilter) ? $scope.orderReferenceNoFilter : '';
        $scope.orderSourceFilter = ($scope.orderSourceFilter) ? $scope.orderSourceFilter : '';
        $scope.emailFilter = ($scope.emailFilter) ? $scope.emailFilter : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.purchaseFromDateFilter = ($scope.purchaseFromDateFilter) ? $scope.purchaseFromDateFilter : '';
        $scope.purchaseToDateFilter = ($scope.purchaseToDateFilter) ? $scope.purchaseToDateFilter : '';
        $scope.expiryFromDateFilter = ($scope.expiryFromDateFilter) ? $scope.expiryFromDateFilter : '';
        $scope.expiryToDateFilter = ($scope.expiryToDateFilter) ? $scope.expiryToDateFilter : '';
        $scope.licenseGlobalSearch = ($scope.licenseGlobalSearch) ? $scope.licenseGlobalSearch : '';
        $scope.loading = true;
        
        if(!pageNumber){
            pageNumber = 1;
        }

        $scope.totalData = 0;
        let currentPage = pageNumber;
        let limit = $scope.perPage;//10;

        // var filters = 'search=' + $scope.licenseGlobalSearch + '&license_key=' + $scope.licenseFilter + '&license_type=' + $scope.licenseTypeFilter + '&product_name=' + $scope.productNameFilter + '&mac_address=' + $scope.macAddressFilter + '&order_reference_no=' + $scope.orderReferenceNoFilter + '&order_source=' + $scope.orderSourceFilter + '&email=' + $scope.emailFilter + '&status=' + $scope.statusFilter + '&purchased_date_from=' + $scope.purchaseFromDateFilter + '&purchased_date_to=' + $scope.purchaseToDateFilter + '&expiry_from_date=' + $scope.expiryFromDateFilter + '&expiry_to_date=' + $scope.expiryToDateFilter;

        let payload = {
            'page_no': pageNumber,
            'limit': limit,
            'search': $scope.licenseGlobalSearch,
            'license_key': $scope.licenseFilter,
            'license_type': $scope.licenseTypeFilter,
            'product_name': $scope.productNameFilter,
            'mac_address': $scope.macAddressFilter,
            'order_reference_no': $scope.orderReferenceNoFilter,
            'order_source': $scope.orderSourceFilter,
            'email': $scope.emailFilter,
            'status': $scope.statusFilter,
            'purchased_date_from': $scope.purchaseFromDateFilter,
            'purchased_date_to': $scope.purchaseToDateFilter,
            'expiry_from_date': $scope.expiryFromDateFilter,
            'expiry_to_date': $scope.expiryToDateFilter,
            'type': $scope.activeTab

        }
        // let url = 'list/active/product/licenses';
        // if($scope.activeTab == 'PACKAGE'){
        let url = 'list/active/package/licenses';
        // }
        $http({
            // url: API_URL + 'get/licenses?' + filters + '&page=' + pageNumber,
            // method: 'GET',
            url: API_URL + url,
            method: 'POST',
            data: payload
        }).then( function success(response) {
            var responseData =  response.data.data;
            // console.log(responseData);
            // $scope.licenselist = responseData.data;
            // $scope.dataFrom = responseData.from;
            // $scope.dataTo = responseData.to;
            // $scope.totalData = responseData.total;
            // $scope.totalPages = responseData.last_page;
            // $scope.currentPage = responseData.current_page;
            // $scope.lastPage  = responseData.last_page;

            $scope.licenselist = responseData.licenses;

            if($scope.licenselist.length > 0){
                $scope.totalData = responseData.total_count;
                $scope.calculateTotalPages($scope.totalData, currentPage, limit);
            }

            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
        });
    };


    $scope.calculateTotalPages = function(totalRecords, currentPage, limit) {
        totalRecords = Math.max(1, totalRecords);
        limit = Math.max(1, limit);
        $scope.totalPages = Math.ceil(totalRecords / limit);
        $scope.currentPage = Math.min(Math.max(currentPage, 1), $scope.totalPages);
        $scope.dataFrom = ($scope.currentPage - 1) * limit + 1;
        $scope.dataTo = Math.min($scope.currentPage * limit, totalRecords);
        $scope.lastPage = $scope.totalPages;
        // console.log($scope.lastPage);
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData(null, 'PRODUCT');

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };


    //Get Products Codes
    $scope.getProducts = function() {
        $http({
            url: API_URL + 'get/products?status=ACTIVE&page=all&sort_by=product_name&sort_order=ASC',
            method: 'GET',
        }).then( function success(response) {
            $scope.productCodes = response.data.data.data;
        });
    };

    //Get License Codes
    $scope.getLicenseCodes = function() {
        $http({
            url: API_URL + 'get/licenseTypes?status=AVAILABLE&page=all&sort_by=name&sort_order=ASC',
            method: 'GET',
        }).then( function success(response) {
            $scope.licenseCodes = response.data.data.data;
        });
    };

    //Get Packages
    $scope.getPackages = function() {
        $http({
        url: API_URL + 'get/packages?status=AVAILABLE&page=all&sort_by=package_name&sort_order=ASC',
        method: 'GET',
        }).then( function success(response) {
            $scope.packages = response.data.data.data;
        });
    };

    $scope.getCodes = function() {
        $scope.getPackages();
        $scope.getProducts();
        $scope.getLicenseCodes();
    };

    //Get License and Customer Details
    $scope.getlicenseDetails = function(id) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/license-details',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            $scope.licenseDetail = response.data.data;
            $scope.loading = false;
        }, function error(response) {
            $scope.loading = false;
        });
    };

    $scope.getYesterdayDate = function() {
        let currentDate = new Date();
        currentDate.setDate(currentDate.getDate() - 1);

        let date = currentDate.getDate().toString();
        date = (date.length > 1) ? date : '0' + date;

        let month = '0' + (currentDate.getMonth() + 1).toString();
        month = (month.length > 1) ? month : '0' + month;

        let year = currentDate.getFullYear().toString();
        let yesterday = year + '-' + month + '-' + date;

        $scope.yesterdayDate = yesterday.toString();
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'showLicenseForm')
        {
            $scope.showLicenseForm.$touched = false;
            $scope.showLicenseForm.$untouched = true;
            $scope.showLicenseForm.$dirty = false;
            $scope.showLicenseForm.$pristine = true;

            $scope.showLicenseForm.password.$touched = false;
            $scope.showLicenseForm.password.$untouched = true;
            $scope.showLicenseForm.password.$dirty = false;
            $scope.showLicenseForm.password.$pristine = true;

            $scope.password = '';
        }

        else if (formName == 'deactivateLicenseForm')
        {
            $scope.deactivateLicenseForm.$touched = false;
            $scope.deactivateLicenseForm.$untouched = true;
            $scope.deactivateLicenseForm.$dirty = false;
            $scope.deactivateLicenseForm.$pristine = true;

            $scope.deactivateLicenseForm.deactivateType.$touched = false;
            $scope.deactivateLicenseForm.deactivateType.$untouched = true;
            $scope.deactivateLicenseForm.deactivateType.$dirty = false;
            $scope.deactivateLicenseForm.deactivateType.$pristine = true;

            $scope.deactivateType = '';
        }

        else if (formName == 'activateLicenseForm')
        {
            $scope.activateLicenseForm.$touched = false;
            $scope.activateLicenseForm.$untouched = true;
            $scope.activateLicenseForm.$dirty = false;
            $scope.activateLicenseForm.$pristine = true;

            $scope.activateLicenseForm.activateType.$touched = false;
            $scope.activateLicenseForm.activateType.$untouched = true;
            $scope.activateLicenseForm.activateType.$dirty = false;
            $scope.activateLicenseForm.activateType.$pristine = true;

            $scope.activateType = '';
        }

        else if (formName == 'deleteLicenseForm')
        {
            $scope.deleteLicenseForm.$touched = false;
            $scope.deleteLicenseForm.$untouched = true;
            $scope.deleteLicenseForm.$dirty = false;
            $scope.deleteLicenseForm.$pristine = true;

            $scope.deleteLicenseForm.userPassword.$touched = false;
            $scope.deleteLicenseForm.userPassword.$untouched = true;
            $scope.deleteLicenseForm.userPassword.$dirty = false;
            $scope.deleteLicenseForm.userPassword.$pristine = true;

            $scope.userPassword = '';
            $scope.deleteType = '';
        }

        else if (formName == 'renewForm')
        {
            $scope.renewForm.$touched = false;
            $scope.renewForm.$untouched = true;
            $scope.renewForm.$dirty = false;
            $scope.renewForm.$pristine = true;

            $scope.renewForm.renewLicenseCode.$touched = false;
            $scope.renewForm.renewLicenseCode.$untouched = true;
            $scope.renewForm.renewLicenseCode.$dirty = false;
            $scope.renewForm.renewLicenseCode.$pristine = true;

            $scope.renewForm.renewalType.$touched = false;
            $scope.renewForm.renewalType.$untouched = true;
            $scope.renewForm.renewalType.$dirty = false;
            $scope.renewForm.renewalType.$pristine = true;

            $scope.renewLicenseCode = '';
            $scope.renewalType = '';
        }

        else if (formName == 'generateLicenseForm')
        {
            $scope.generateLicenseForm.$touched = false;
            $scope.generateLicenseForm.$untouched = true;
            $scope.generateLicenseForm.$dirty = false;
            $scope.generateLicenseForm.$pristine = true;

            $scope.generateLicenseForm.productCode.$touched = false;
            $scope.generateLicenseForm.productCode.$untouched = true;
            $scope.generateLicenseForm.productCode.$dirty = false;
            $scope.generateLicenseForm.productCode.$pristine = true;

            $scope.generateLicenseForm.licenseCode.$touched = false;
            $scope.generateLicenseForm.licenseCode.$untouched = true;
            $scope.generateLicenseForm.licenseCode.$dirty = false;
            $scope.generateLicenseForm.licenseCode.$pristine = true;

            $scope.generateLicenseForm.counts.$touched = false;
            $scope.generateLicenseForm.counts.$untouched = true;
            $scope.generateLicenseForm.counts.$dirty = false;
            $scope.generateLicenseForm.counts.$pristine = true;

            $scope.generateLicenseForm.orderSource.$touched = false;
            $scope.generateLicenseForm.orderSource.$untouched = true;
            $scope.generateLicenseForm.orderSource.$dirty = false;
            $scope.generateLicenseForm.orderSource.$pristine = true;

            $scope.generateLicenseForm.orderReferenceNo.$touched = false;
            $scope.generateLicenseForm.orderReferenceNo.$untouched = true;
            $scope.generateLicenseForm.orderReferenceNo.$dirty = false;
            $scope.generateLicenseForm.orderReferenceNo.$pristine = true;

            $scope.generateLicenseForm.orderInfo.$touched = false;
            $scope.generateLicenseForm.orderInfo.$untouched = true;
            $scope.generateLicenseForm.orderInfo.$dirty = false;
            $scope.generateLicenseForm.orderInfo.$pristine = true;

            $scope.generateLicenseForm.orderTime.$touched = false;
            $scope.generateLicenseForm.orderTime.$untouched = true;
            $scope.generateLicenseForm.orderTime.$dirty = false;
            $scope.generateLicenseForm.orderTime.$pristine = true;

            $scope.generateLicenseForm.email.$touched = false;
            $scope.generateLicenseForm.email.$untouched = true;
            $scope.generateLicenseForm.email.$dirty = false;
            $scope.generateLicenseForm.email.$pristine = true;

            $scope.generateLicenseForm.firstName.$touched = false;
            $scope.generateLicenseForm.firstName.$untouched = true;
            $scope.generateLicenseForm.firstName.$dirty = false;
            $scope.generateLicenseForm.firstName.$pristine = true;

            $scope.generateLicenseForm.lastName.$touched = false;
            $scope.generateLicenseForm.lastName.$untouched = true;
            $scope.generateLicenseForm.lastName.$dirty = false;
            $scope.generateLicenseForm.lastName.$pristine = true;

            $scope.generateLicenseForm.phoneNo.$touched = false;
            $scope.generateLicenseForm.phoneNo.$untouched = true;
            $scope.generateLicenseForm.phoneNo.$dirty = false;
            $scope.generateLicenseForm.phoneNo.$pristine = true;

            $scope.productCode = '';
            $scope.licenseCode = '';
            $scope.counts = 1;
            $scope.orderSource = '';
            $scope.orderReferenceNo = '';
            $scope.orderInfo = '';
            $scope.orderTime = '';
            $scope.email = '';
            $scope.firstName = '';
            $scope.lastName = '';
            $scope.phoneNo = '';
        }

        else if (formName == 'resetMacForm')
        {
            $scope.resetMacForm.$touched = false;
            $scope.resetMacForm.$untouched = true;
            $scope.resetMacForm.$dirty = false;
            $scope.resetMacForm.$pristine = true;

            $scope.resetMacForm.resetType.$touched = false;
            $scope.resetMacForm.resetType.$untouched = true;
            $scope.resetMacForm.resetType.$dirty = false;
            $scope.resetMacForm.resetType.$pristine = true;

            $scope.resetType = '';
        }
    };

    //Update License and Customer Details
    $scope.updateCustomerDetails = function() {

        $http({
            url: API_URL + 'license/customer-update',
            method: 'POST',
            data: {
                license_key: $scope.updateLicenseKey,
                mac_address: $scope.updateMacAddress,
                email: $scope.updateEmail,
                first_name: $scope.updateFirstName,
                last_name: $scope.updateLastName,
                phone_no: $scope.updatePhoneNo
            },
        }).then( function success(response) {
            window.location.href = window.location.href;
        }, function error(response) {
            var errors = response.data.data.error, errorData = '';
            var element = angular.element($document[0].getElementById('updateCustomer-error-res'));
            angular.forEach(errors, function (value, key) {
                errorData += '<div>* ' + value + '</div>';
            });
            element.html(errorData);
        });
    };

    $scope.userPasswordToggle = true;
    $scope.toggleUserPassword = function() {
        $scope.userPasswordToggle = !$scope.userPasswordToggle;
    };

    $scope.deleteLicenseKey = function() {
        var element = angular.element($document[0].getElementById('deleteLicenseKey-error-res'));
        element.text('');
        $scope.loading = true;

        $http({
            url: API_URL + 'delete/license',
            method: 'POST',
            data: {
                id : $scope.licenseId,
                password : $scope.userPassword,
                delete_type: ($scope.deleteType) ? $scope.deleteType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>deleted</b> successfully.');

            let responseData = response.data.data;

            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist.splice(index, 1);
                    $scope.totalData -= 1;
                    $scope.dataTo -= 1;
                }
            });

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('deleteLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    $scope.getLicenseHistory = function(id) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/license/history',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            $scope.licenseHistoryData = response.data.data;
            $scope.loading = false;
        }, function error(response) {

        });
    };

    $scope.getLicenseProduct = function(licenseKey) {
        $scope.selectedLicenseKey = licenseKey;
        $scope.getProductByKey(1);
    }

    $scope.getProductByKey = function(pageNumber) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/licenses?license_key=' + $scope.selectedLicenseKey + '&page='+pageNumber,
            method: 'GET',
        }).then( function success(response) {
            // console.log(response);
            let responseData = response.data.data;
            $scope.dataSubFrom = responseData.from;
            $scope.licenseProductData = responseData.data || [];
            $scope.subCurrentPage = responseData.current_page;
            $scope.totalSubPages = responseData.last_page;
            // $scope.licenseProductData = response.data.data || [];
            $scope.loading = false;
        }, function error(response) {
            $scope.loading = false;
        });
    };

    $scope.sortByProductField = function(field) {
        $scope.sortByProduct = field;
        $scope.reverseProduct = !$scope.reverseProduct;
    };


    // Sort By function
    $scope.sortByHistoryField = function(field) {
        $scope.sortByHistory = field;
        $scope.reverseHistory = !$scope.reverseHistory;
    };


    $scope.activateLicense = function() {
        $http({
            url: API_URL + 'license/activate',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                activate_type: ($scope.activateType) ? $scope.activateType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>activated</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });
        }, function error(response) {

        });
    };


    $scope.deactivateLicense = function() {
        $http({
            url: API_URL + 'license/deactivate',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                deactivate_type: ($scope.deactivateType) ? $scope.deactivateType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>deactivated</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });

        }, function error(response) {

        });
    };

    /****  Show License Key Section ****/

    $scope.passwordToggle = true;
    $scope.togglePassword = function() {
        $scope.passwordToggle = !$scope.passwordToggle;
    };

    $scope.getLicenseId = function(license, columnId, alertType) {
        $scope.licenseId = license.license_uuid;
        $scope.licenseData = license;
        $scope.columnId = columnId;
        $scope.alertType = alertType;
        $scope.packageFlag = license.package;
        // console.log(license);
        if(alertType == 'ResetMACAlert'){
            $scope.clearFormData('resetMacForm');
            $scope.resetType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT' : "PACKAGE"
            $scope.resetMacForm.resetType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT' : "PACKAGE"
        }
        if (alertType == 'DeactivateAlert') {
            $scope.clearFormData('deactivateLicenseForm')
            $scope.deactivateType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT_CODE' : "PACKAGE"
        }
        if (alertType == 'ActivateAlert') {
            $scope.clearFormData('activateLicenseForm')
            $scope.activateType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT_CODE' : "PACKAGE"
        }
        if (alertType == 'DeleteAlert') {
            $scope.clearFormData('deleteLicenseForm')
            $scope.deleteType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT_CODE' : "PACKAGE"
        }
    };

    $scope.getLicenseIdSub = function(license, columnId, alertType) {
        $scope.licenseId = license.license_uuid;
        $scope.licenseData = license;
        $scope.columnId = columnId;
        $scope.alertType = alertType;
        $scope.packageFlag = license.package;
    }

    $scope.getRenewData = function(license, product) {
        $scope.renewLicenseId = license.license_uuid;
        $scope.renewLicense = license.hashed_license_key;
        $scope.renewProductCode = product.product_code;
        $scope.renewPackageFlag = license.package;
        $scope.renewLicenseCode = '';
        $scope.renewalType = '';
        $scope.renewalType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT_CODE' : "PACKAGE"
    };


    $scope.copyToClipboard = function (currentElement, targetId)
    {
        var r = document.createRange();
        r.selectNode(document.getElementById(targetId));
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(r);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
    };


    /*  // copyToClipboard Version 2
    $scope.copyToClipboard = function(currentElement, targetId) {
        angular.element(currentElement).attr('title', 'Copied');
        var targetElement = angular.element($document[0].getElementById(targetId));
        navigator.clipboard.writeText(targetElement.text());
    };
    */

    $scope.renewLicenseDetails = function() {
        var element = angular.element($document[0].getElementById('renewLicense-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'license/renewal',
            method: 'POST',
            data: {
                id: $scope.renewLicenseId,
                license_code: $scope.renewLicenseCode,
                renewal_type: ($scope.renewalType) ? $scope.renewalType : 'PRODUCT_CODE'
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#renewModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>renewed</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('renewLicense-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    $scope.getActualLicenseKey = function() {
        var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'get/actual-license',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                password: $scope.password
            }
        }).then( function success(response) {
            var responseData = response.data.data;
            var licenseColumn = angular.element($document[0].getElementById('LicenseKey-field-' + $scope.columnId));
            licenseColumn.text(responseData.license_key);

            angular.element($document[0].querySelector('#LicenseKey-field-' + $scope.columnId + ' ~ i.fa-eye')).remove();
            angular.element($document[0].querySelector('#LicenseKey-field-' + $scope.columnId + ' ~ i.fa-copy')).removeClass('d-none');

            /**** Close Modal ****/
            document.querySelector('#showLicenseKeyModal .btn-close').click();

            $scope.loading = false;

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    $scope.getSubActualLicenseKey = function() {
        var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'get/actual-license',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                password: $scope.password
            }
        }).then( function success(response) {
            var responseData = response.data.data;
            var licenseColumn = angular.element($document[0].getElementById('LicenseKey-field-sub-' + $scope.columnId));
            licenseColumn.text(responseData.license_key);

            angular.element($document[0].querySelector('#LicenseKey-field-sub-' + $scope.columnId + ' ~ i.fa-eye')).remove();
            angular.element($document[0].querySelector('#LicenseKey-field-sub-' + $scope.columnId + ' ~ i.fa-copy')).removeClass('d-none');

            /**** Close Modal ****/
            document.querySelector('#showSubLicenseKeyModal .btn-close').click();

            $scope.loading = false;

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('getSubActualLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };


    /****  Reset MAC Address ****/

    $scope.resetMACAddress = function() {
        $scope.loading = true;

        $http({
            url: API_URL + 'reset/mac-address',
            method: 'POST',
            data: {
                id: $scope.licenseId
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            document.querySelector('#licenseDetailModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'MAC Address has been <b>reset</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.licenselist.findIndex(x => x.license_uuid == responseData.license_uuid);

            if (index > -1)
            {
                $scope.licenselist[index] = responseData;
            }

        }, function error(response) {
            var error = response.data.data.error;
            $scope.loading = false;
        });
    };

     /****  Reset MAC Address V2 Api****/

     $scope.resetMAC = function() {
        $scope.loading = true;

        // console.log($scope.licenseId, $scope.resetType);
        // return;

        $http({
            url: API_URL + 'v2/reset/mac-address',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                type:$scope.resetType
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            document.querySelector('#licenseDetailModal .btn-close').click();
            $scope.loading = false;
            console.log(response);

            // Show the notification
            notificationAlert('Success', 'MAC Address has been <b>reset</b> successfully.');

            let responseData = response.data.data;

            responseData.forEach(responseItem => {
                const existingItemIndex = $scope.licenselist.findIndex(existingItem => existingItem.license_uuid === responseItem.license_uuid);                
                if (existingItemIndex !== -1) {
                  $scope.licenselist[existingItemIndex] = { ...$scope.licenselist[existingItemIndex], ...responseItem };
                }
              });

        }, function error(response) {
            // var error = response.data.data.error;
            // console.log(error);
            const { data : { error }, message} = response.data;
            $scope.loading = false;
            notificationAlert('Failure', error[0] || message);
        });
    };

});


app.controller('expiredLicenseController', function($scope, $http, $document, $timeout, API_URL) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];
    $scope.counts = 1;

    // For get form data from ng-if content
    $scope.form = {};

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
    };

    $scope.excludeTrial = true;
    $scope.toggleExcludeTrial = function() {
        $scope.excludeTrial = !$scope.excludeTrial;
        $scope.getPaginateData();
    };

    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };

    $scope.perPage = 10;
    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    //Get licenses
    $scope.getPaginateData = function(pageNumber) {
        $scope.licenseFilter = ($scope.licenseFilter) ? $scope.licenseFilter : '';
        $scope.licenseTypeFilter = ($scope.licenseTypeFilter) ? $scope.licenseTypeFilter : '';
        $scope.productNameFilter = ($scope.productNameFilter) ? $scope.productNameFilter : '';
        $scope.macAddressFilter = ($scope.macAddressFilter) ? $scope.macAddressFilter : '';
        $scope.orderReferenceNoFilter = ($scope.orderReferenceNoFilter) ? $scope.orderReferenceNoFilter : '';
        $scope.orderSourceFilter = ($scope.orderSourceFilter) ? $scope.orderSourceFilter : '';
        $scope.emailFilter = ($scope.emailFilter) ? $scope.emailFilter : '';
        $scope.statusFilter = 'EXPIRED';
        $scope.purchaseFromDateFilter = ($scope.purchaseFromDateFilter) ? $scope.purchaseFromDateFilter : '';
        $scope.purchaseToDateFilter = ($scope.purchaseToDateFilter) ? $scope.purchaseToDateFilter : '';
        $scope.expiryFromDateFilter = ($scope.expiryFromDateFilter) ? $scope.expiryFromDateFilter : '';
        $scope.expiryToDateFilter = ($scope.expiryToDateFilter) ? $scope.expiryToDateFilter : '';
        $scope.loading = true;

        if(pageNumber === undefined){
            pageNumber = '1';
        }

        var filters = 'license_key=' + $scope.licenseFilter + '&license_type=' + $scope.licenseTypeFilter + '&product_name=' + $scope.productNameFilter + '&mac_address=' + $scope.macAddressFilter + '&order_reference_no=' + $scope.orderReferenceNoFilter + '&order_source=' + $scope.orderSourceFilter + '&email=' + $scope.emailFilter + '&status=' + $scope.statusFilter + '&purchased_date_from=' + $scope.purchaseFromDateFilter + '&purchased_date_to=' + $scope.purchaseToDateFilter + '&expiry_from_date=' + $scope.expiryFromDateFilter + '&expiry_to_date=' + $scope.expiryToDateFilter + '&exclude_trial=' + $scope.excludeTrial;

        $http({
            url: API_URL + 'get/licenses?' + filters + '&page=' + pageNumber+ '&per_page=' + $scope.perPage,
            method: 'GET',
        }).then( function success(response) {
            var responseData =  response.data.data;
            $scope.licenselist = responseData.data;
            $scope.dataFrom = responseData.from;
            $scope.dataTo = responseData.to;
            $scope.totalData = responseData.total;
            $scope.totalPages = responseData.last_page;
            $scope.currentPage = responseData.current_page;
            $scope.lastPage  = responseData.last_page;
            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
        });
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData();

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'showLicenseForm')
        {
            $scope.showLicenseForm.$touched = false;
            $scope.showLicenseForm.$untouched = true;
            $scope.showLicenseForm.$dirty = false;
            $scope.showLicenseForm.$pristine = true;

            $scope.showLicenseForm.password.$touched = false;
            $scope.showLicenseForm.password.$untouched = true;
            $scope.showLicenseForm.password.$dirty = false;
            $scope.showLicenseForm.password.$pristine = true;

            $scope.password = '';
        }

        else if (formName == 'deleteLicenseForm')
        {
            $scope.deleteLicenseForm.$touched = false;
            $scope.deleteLicenseForm.$untouched = true;
            $scope.deleteLicenseForm.$dirty = false;
            $scope.deleteLicenseForm.$pristine = true;

            $scope.deleteLicenseForm.userPassword.$touched = false;
            $scope.deleteLicenseForm.userPassword.$untouched = true;
            $scope.deleteLicenseForm.userPassword.$dirty = false;
            $scope.deleteLicenseForm.userPassword.$pristine = true;

            $scope.userPassword = '';
        }

        else if (formName == 'renewForm')
        {
            $scope.renewForm.$touched = false;
            $scope.renewForm.$untouched = true;
            $scope.renewForm.$dirty = false;
            $scope.renewForm.$pristine = true;

            $scope.renewForm.renewLicenseCode.$touched = false;
            $scope.renewForm.renewLicenseCode.$untouched = true;
            $scope.renewForm.renewLicenseCode.$dirty = false;
            $scope.renewForm.renewLicenseCode.$pristine = true;

            $scope.renewForm.renewalType.$touched = false;
            $scope.renewForm.renewalType.$untouched = true;
            $scope.renewForm.renewalType.$dirty = false;
            $scope.renewForm.renewalType.$pristine = true;

            $scope.renewLicenseCode = '';
            $scope.renewalType = '';
        }

    };

    $scope.userPasswordToggle = true;
    $scope.toggleUserPassword = function() {
        $scope.userPasswordToggle = !$scope.userPasswordToggle;
    };

    $scope.deleteLicenseKey = function() {
        var element = angular.element($document[0].getElementById('deleteLicenseKey-error-res'));
        element.text('');
        $scope.loading = true;

        $http({
            url: API_URL + 'delete/license',
            method: 'POST',
            data: {
                id : $scope.licenseId,
                password : $scope.userPassword,
                delete_type: ($scope.deleteType) ? $scope.deleteType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            let responseData = response.data.data;
            let index = $scope.licenselist.findIndex(x => x.license_uuid == responseData.license_uuid);

            if (index > -1)
            {
                $scope.licenselist.splice(index, 1);
            }
        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('deleteLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };


    $scope.getLicenseHistory = function(id) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/license/history',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            $scope.licenseHistoryData = response.data.data;
            $scope.loading = false;
        }, function error(response) {

        });
    };

    // Sort By function
    $scope.sortByHistoryField = function(field) {
        $scope.sortByHistory = field;
        $scope.reverseHistory = !$scope.reverseHistory;
    };


    $scope.activateLicense = function(id) {
        $http({
            url: API_URL + 'license/activate',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            let responseData = response.data.data;
            let index = $scope.licenselist.findIndex(x => x.license_uuid == responseData.license_uuid);

            if (index > -1)
            {
                $scope.licenselist[index] = responseData;
            }
        }, function error(response) {

        });
    };


    $scope.deactivateLicense = function(id) {
        $http({
            url: API_URL + 'license/deactivate',
            method: 'POST',
            data: {
                id: id

            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            let responseData = response.data.data;
            let index = $scope.licenselist.findIndex(x => x.license_uuid == responseData.license_uuid);

            if (index > -1)
            {
                $scope.licenselist[index] = responseData;
            }
        }, function error(response) {

        });
    };

    //Get License Codes
    $scope.getLicenseCodes = function() {
        $http({
            url: API_URL + 'get/licenseTypes?status=AVAILABLE&page=all&sort_by=name&sort_order=ASC',
            method: 'GET',
        }).then( function success(response) {
            $scope.licenseCodes = response.data.data.data;
        });
    };

    //Get License and Customer Details
    $scope.getlicenseDetails = function(id) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/license-details',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            $scope.licenseDetail = response.data.data;
            $scope.loading = false;
        }, function error(response) {
            $scope.loading = false;
        });
    };

    $scope.getRenewData = function(license, product) {
        $scope.renewLicenseId = license.license_uuid;
        $scope.renewLicense = license.hashed_license_key;
        $scope.renewProductCode = product.product_code;
        $scope.renewPackageFlag = license.package;
        $scope.renewLicenseCode = '';
        $scope.renewalType = '';
    };

    $scope.renewLicenseDetails = function() {
        var element = angular.element($document[0].getElementById('renewLicense-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'license/renewal',
            method: 'POST',
            data: {
                id: $scope.renewLicenseId,
                license_code: $scope.renewLicenseCode,
                renewal_type: ($scope.renewalType) ? $scope.renewalType : 'PRODUCT_CODE'
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#renewModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>renewed</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist.splice(index, 1);
                    $scope.totalData -= 1;
                    $scope.dataTo -= 1;
                }
            });
        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('renewLicense-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    /****  Show License Key Section ****/

    $scope.passwordToggle = true;
    $scope.togglePassword = function() {
        $scope.passwordToggle = !$scope.passwordToggle;
    };

    $scope.getLicenseId = function(license, columnId, alertType) {
        $scope.licenseId = license.license_uuid;
        $scope.columnId = columnId;
        $scope.userPassword = '';
        $scope.alertType = alertType;
    };

    $scope.copyToClipboard = function (currentElement, targetId)
    {
        var r = document.createRange();
        r.selectNode(document.getElementById(targetId));
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(r);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
    };


    /*  // copyToClipboard Version 2
    $scope.copyToClipboard = function(currentElement, targetId) {
        angular.element(currentElement).attr('title', 'Copied');
        var targetElement = angular.element($document[0].getElementById(targetId));
        navigator.clipboard.writeText(targetElement.text());
    };
    */

    $scope.getActualLicenseKey = function() {
        var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'get/actual-license',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                password: $scope.password
            }
        }).then( function success(response) {
            var responseData = response.data.data;
            var licenseColumn = angular.element($document[0].getElementById('LicenseKey-field-' + $scope.columnId));
            licenseColumn.text(responseData.license_key);

            angular.element($document[0].querySelector('#LicenseKey-field-' + $scope.columnId + ' ~ i.fa-eye')).remove();
            angular.element($document[0].querySelector('#LicenseKey-field-' + $scope.columnId + ' ~ i.fa-copy')).removeClass('d-none');

            /**** Close Modal ****/
            document.querySelector('#showLicenseKeyModal .btn-close').click();

            $scope.loading = false;

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

});


app.controller('roleController', function($scope, $http, $document, $timeout, API_URL) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
    };


    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };


    /****  Get licenseTypes ****/
    $scope.getPaginateData = function(pageNumber) {
        $scope.nameFilter = ($scope.nameFilter) ? $scope.nameFilter : '';
        $scope.codeFilter = ($scope.codeFilter) ? $scope.codeFilter : '';
        $scope.descriptionFilter = ($scope.descriptionFilter) ? $scope.descriptionFilter : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.loading = true;

        if(pageNumber === undefined){
            pageNumber = '1';
        }

        var filters = 'role_name=' + $scope.nameFilter + '&role_code=' + $scope.codeFilter + '&role_description=' + $scope.descriptionFilter + '&status=' + $scope.statusFilter;

        $http({
            url: API_URL + 'get/roles?' + filters + '&page=' + pageNumber,
            method: 'GET'
        }).then( function success(response) {
            var responseData = response.data.data;
            $scope.roles = responseData.data;
            $scope.dataFrom = responseData.from;
            $scope.dataTo = responseData.to;
            $scope.totalData = responseData.total;
            $scope.totalPages   = responseData.last_page;
            $scope.currentPage  = responseData.current_page;
            $scope.lastPage  = responseData.last_page;
            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#generateLicenseModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
        });
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData(pageNumber);

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };

    $scope.generateRoleCode = function(roleName) {
        $scope.roleCode = roleName.replace(/ /g, '_').toUpperCase();
    };

    /**** Add Role *****/
    $scope.addRole = function() {
        var element = angular.element($document[0].getElementById('addRole-error-res'));
        element.html('');

        $http({
            url: API_URL + 'add/role',
            method: 'POST',
            data: {
                role_name: $scope.roleName,
                role_code: $scope.roleCode,
                role_description: $scope.description,
                status: $scope.status
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#addRoleModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'Role has been <b>added</b> successfully.');

        }, function error(response) {
            var errors = response.data.data.error, errorData = '';
            var element = angular.element($document[0].getElementById('addRole-error-res'));
            angular.forEach(errors, function (value, key) {
                errorData += '<div>* ' + value + '</div>';
            });
            element.html(errorData);
        });
    };

    $scope.getRole = function(role) {
        $scope.updateRoleName = role.role_name;
        $scope.updateRoleCode = role.role_code;
        $scope.updateDescription = role.role_description;
        $scope.updateStatus = role.status;
        $scope.roleId = role.role_uuid;
    };

    $scope.updateRole = function() {
        var element = angular.element($document[0].getElementById('updateRole-error-res'));
        element.html('');

        $http({
            url: API_URL + 'update/role',
            method: 'POST',
            data: {
                id: $scope.roleId,
                role_name: $scope.updateRoleName,
                role_code: $scope.updateRoleCode,
                role_description: $scope.updateDescription,
                status: $scope.updateStatus,
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#updateRoleModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'Role has been <b>updated</b> successfully.');

        }, function error(response) {
            var errors = response.data.data.error, errorData = '';
            var element = angular.element($document[0].getElementById('updateRole-error-res'));
            angular.forEach(errors, function (value, key) {
                errorData += '<div>* ' + value + '</div>';
            });
            element.html(errorData);
        });
    }
});


app.controller('userController', function($scope, $http, $document, $timeout, API_URL) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
    };


    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };

    $scope.perPage = 10;

    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    $scope.showPasswordFields = false;
    $scope.togglePassword = function () {
        $scope.passwordToggle = !$scope.passwordToggle;
    };

    $scope.toggleCnfmPassword = function () {
        $scope.cnfmPasswordToggle = !$scope.cnfmPasswordToggle;
    };



    /****  Get licenseTypes ****/
    $scope.getPaginateData = function(pageNumber) {
        $scope.nameFilter = ($scope.nameFilter) ? $scope.nameFilter : '';
        $scope.userNameFilter = ($scope.userNameFilter) ? $scope.userNameFilter : '';
        $scope.emailFilter = ($scope.emailFilter) ? $scope.emailFilter : '';
        $scope.phoneFilter = ($scope.phoneFilter) ? $scope.phoneFilter : '';
        $scope.userTypeFilter = ($scope.userTypeFilter) ? $scope.userTypeFilter : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.loading = true;

        if(pageNumber === undefined){
            pageNumber = '1';
        }

        var filters = 'name=' + $scope.nameFilter + '&user_name=' + $scope.userNameFilter + '&email=' + $scope.emailFilter + '&phone=' + $scope.phoneFilter + '&user_type=' + $scope.userTypeFilter + '&status=' + $scope.statusFilter;

        $http({
            url: API_URL + 'get/users?' + filters + '&page=' + pageNumber + '&per_page=' + $scope.perPage,
            method: 'GET'
        }).then( function success(response) {
            console.log('API_URL', API_URL);
            console.log('response', response);

            var responseData = response.data.data;
            $scope.users = responseData.data;
            $scope.dataFrom = responseData.from;
            $scope.dataTo = responseData.to;
            $scope.totalData = responseData.total;
            $scope.totalPages   = responseData.last_page;
            $scope.currentPage  = responseData.current_page;
            $scope.lastPage  = responseData.last_page;
            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
        });
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData(pageNumber);

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };


    $scope.getUserRoles = function() {
        $http({
            url: API_URL + 'get/roles?' + '&page=All',
            method: 'GET'
        }).then( function success(response) {
            var responseData = response.data.data;
            $scope.userRoles = responseData.data;
            $scope.loading = false;

        }, function error(response) {
            $scope.loading = false;
        });
    };

    $scope.getUserRoles();

    $scope.passwordToggle = true;
    $scope.togglePassword = function() {
        $scope.passwordToggle = !$scope.passwordToggle;
    };

    $scope.cnfmPasswordToggle = true;
    $scope.toggleCnfmPassword = function() {
        $scope.cnfmPasswordToggle = !$scope.cnfmPasswordToggle;
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'addUserForm')
        {
            $scope.addUserForm.$touched = false;
            $scope.addUserForm.$untouched = true;
            $scope.addUserForm.$dirty = false;
            $scope.addUserForm.$pristine = true;

            $scope.addUserForm.firstName.$touched = false;
            $scope.addUserForm.firstName.$untouched = true;
            $scope.addUserForm.firstName.$dirty = false;
            $scope.addUserForm.firstName.$pristine = true;

            $scope.addUserForm.lastName.$touched = false;
            $scope.addUserForm.lastName.$untouched = true;
            $scope.addUserForm.lastName.$dirty = false;
            $scope.addUserForm.lastName.$pristine = true;

            $scope.addUserForm.email.$touched = false;
            $scope.addUserForm.email.$untouched = true;
            $scope.addUserForm.email.$dirty = false;
            $scope.addUserForm.email.$pristine = true;

            $scope.addUserForm.phone.$touched = false;
            $scope.addUserForm.phone.$untouched = true;
            $scope.addUserForm.phone.$dirty = false;
            $scope.addUserForm.phone.$pristine = true;

            $scope.addUserForm.userName.$touched = false;
            $scope.addUserForm.userName.$untouched = true;
            $scope.addUserForm.userName.$dirty = false;
            $scope.addUserForm.userName.$pristine = true;

            $scope.addUserForm.userType.$touched = false;
            $scope.addUserForm.userType.$untouched = true;
            $scope.addUserForm.userType.$dirty = false;
            $scope.addUserForm.userType.$pristine = true;

            $scope.addUserForm.password.$touched = false;
            $scope.addUserForm.password.$untouched = true;
            $scope.addUserForm.password.$dirty = false;
            $scope.addUserForm.password.$pristine = true;

            $scope.addUserForm.confirmPassword.$touched = false;
            $scope.addUserForm.confirmPassword.$untouched = true;
            $scope.addUserForm.confirmPassword.$dirty = false;
            $scope.addUserForm.confirmPassword.$pristine = true;

            $scope.addUserForm.status.$touched = false;
            $scope.addUserForm.status.$untouched = true;
            $scope.addUserForm.status.$dirty = false;
            $scope.addUserForm.status.$pristine = true;

            $scope.firstName = '';
            $scope.lastName = '';
            $scope.email = '';
            $scope.phone = '';
            $scope.userName = '';
            $scope.userType = '';
            $scope.password = '';
            $scope.confirmPassword = '';
            $scope.status = '';
        }
    };

    /**** Add User *****/
    $scope.addUser = function() {
        var element = angular.element($document[0].getElementById('addUser-error-res'));
        element.html('');

        $http({
            url: API_URL + 'add/user',
            method: 'POST',
            data: {
                first_name: $scope.firstName,
                last_name: $scope.lastName,
                user_name: $scope.userName,
                user_type: $scope.userType,
                email: $scope.email,
                phone: $scope.phone,
                password: $scope.password,
                confirm_password: $scope.confirmPassword,
                status: $scope.status
            },
        }).then(function success(response) {
            /**** Close Modal ****/
            document.querySelector('#addUserModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'User has been <b>added</b> successfully.');

            let responseData = response.data.data;
            $scope.users.unshift(responseData);
            $scope.totalData += 1;
            $scope.dataTo += 1;

        }, function error(response) {
            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#addUserModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('addUser-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    };

    $scope.getUser = function(user) {
        console.log('user', user);
        var element = angular.element($document[0].getElementById('updateUser-error-res'));
        element.html('');

        $scope.updateFirstName = user.first_name;
        $scope.updateLastName = user.last_name;
        $scope.updateUserName = user.user_name;
        $scope.updateUserType = user.user_type;
        $scope.updateEmail = user.email;
        $scope.updatePhone = user.phone;
        $scope.updateStatus = user.status;

        $scope.userId = user.user_uuid;
    };

    /*$scope.updateUser = function() {
        var element = angular.element($document[0].getElementById('updateUser-error-res'));
        element.html('');

        $http({
            url: API_URL + 'update/user',
            method: 'POST',
            data: {
                id: $scope.userId,
                first_name: $scope.updateFirstName,
                last_name: $scope.updateLastName,
                user_name: $scope.updateUserName,
                user_type: $scope.updateUserType,
                email: $scope.updateEmail,
                phone: $scope.updatePhone,
                status: $scope.updateStatus,
                change_password: $scope.showPasswordFields ? 1 : 0
            },
        }).then(function success(response) {
            /**** Close Modal ****/
    /*        document.querySelector('#updateModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'User has been <b>updated</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.users.findIndex(x => x.user_uuid == responseData.user_uuid);

            if (index > -1)
            {
                $scope.users[index] = responseData;
            }
        }, function error(response) {
            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
    /*            document.querySelector('#updateModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('updateUser-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    }*/

    $scope.updateUser = function() {

    var element = angular.element($document[0].getElementById('updateUser-error-res'));
    element.html('');

    let requestData = {
        id: $scope.userId,
        first_name: $scope.updateFirstName,
        last_name: $scope.updateLastName,
        user_name: $scope.updateUserName,
        user_type: $scope.updateUserType,
        email: $scope.updateEmail,
        phone: $scope.updatePhone,
        status: $scope.updateStatus,
        change_password: $scope.showPasswordFields ? 1 : 0
    };

    // Only send password if changing
    if ($scope.showPasswordFields) {
        requestData.password = $scope.password;
        requestData.confirm_password = $scope.confirmPassword;
    }

    $http({
        url: API_URL + 'update/user',
        method: 'POST',
        data: requestData
    }).then(function success(response) {

        document.querySelector('#updateModal .btn-close').click();
        $scope.loading = false;

        notificationAlert('Success', 'User has been <b>updated</b> successfully.');

        let responseData = response.data.data;
        let index = $scope.users.findIndex(x => x.user_uuid == responseData.user_uuid);

        if (index > -1) {
            $scope.users[index] = responseData;
        }

    }, function error(response) {

        var errors = response.data.data.error, errorData = '';
        angular.forEach(errors, function (value) {
            errorData += '<div>* ' + value + '</div>';
        });

        element.html(errorData);
    });
};

});


app.controller('orderController', function($scope, $http, $document, $timeout, API_URL) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
    };


    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };

    $scope.perPage = 10;
    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    /****  Get Orders ****/
    $scope.getPaginateData = function(pageNumber) {
        $scope.orderIdFilter = ($scope.orderIdFilter) ? $scope.orderIdFilter : '';
        $scope.productNameFilter = ($scope.productNameFilter) ? $scope.productNameFilter : '';
        $scope.licenseTypeFilter = ($scope.licenseTypeFilter) ? $scope.licenseTypeFilter : '';
        $scope.emailFilter = ($scope.emailFilter) ? $scope.emailFilter : '';
        $scope.orderDateFilter = ($scope.orderDateFilter) ? $scope.orderDateFilter : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.loading = true;

        if(pageNumber === undefined){
            pageNumber = '1';
        }

        var filters = 'order_id=' + $scope.orderIdFilter + '&product_name=' + $scope.productNameFilter + '&license_type=' + $scope.licenseTypeFilter + '&customer_email=' + $scope.emailFilter + '&order_date=' + $scope.orderDateFilter + '&order_status=' + $scope.statusFilter;

        $http({
            url: API_URL + 'get/orders?' + filters + '&page=' + pageNumber + '&per_page=' + $scope.perPage,
            method: 'GET'
        }).then( function success(response) {
            var responseData = response.data.data;
            $scope.orders = responseData.data;
            $scope.dataFrom = responseData.from;
            $scope.dataTo = responseData.to;
            $scope.totalData = responseData.total;
            $scope.totalPages   = responseData.last_page;
            $scope.currentPage  = responseData.current_page;
            $scope.lastPage  = responseData.last_page;
            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#generateLicenseModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
        });
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData(pageNumber);

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };

    $scope.getOrderId = function(order, columnId, alertType) {
        $scope.orderId = order.order_uuid;
        $scope.alertType = alertType;
    };

    $scope.syncOrder = function(id) {
        $scope.spinnerLoading = true;

        $http({
            url: API_URL + 'wp/order/sync',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.spinnerLoading = false;

            // Show the notification
            notificationAlert('Success', 'Order has been <b>updated</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.orders.findIndex(x => x.order_uuid == responseData.order_uuid);
            if (index > -1)
            {
                $scope.orders[index] = responseData;
            }

        }, function error(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.spinnerLoading = false;

            // Show the notification
            notificationAlert('Failure', 'There is no recent updates!');
        });
    };

    $scope.exportOrder = function() {
        $scope.loading = true;

        $http({
            url: API_URL + 'export/order',
            method: 'GET',
            responseType: 'arraybuffer'
        }).then(function success(response) {
            var headers = response.headers();
            var data = response.data;

            var filename = headers['x-filename'] || headers['content-disposition'].replace('attachment; filename=', '');
            var contentType = headers['content-type'];

            var linkElement = document.createElement('a');
            try {
                var blob = new Blob([data], { type: contentType });
                var url = window.URL.createObjectURL(blob);

                linkElement.setAttribute('href', url);
                linkElement.setAttribute("download", filename);

                var clickEvent = new MouseEvent("click", {
                    "view": window,
                    "bubbles": true,
                    "cancelable": false
                });
                linkElement.dispatchEvent(clickEvent);
                $scope.loading = false;
            } catch (ex) {
                $scope.loading = false;
            }

        }, function error(response) {
            $scope.loading = false;
        });
    }

});
app.controller('ReportController', function($scope, $http, $document, $timeout, API_URL, $location) {
    var pageNumber, dataFilterTimeout;
    $scope.totalPages = 0;
    $scope.currentPage = 1;
    $scope.lastPage = 1;
    $scope.range = [];
    $scope.counts = 1;
    $scope.expiryFromDateFilter = null;
    $scope.expiryToDateFilter = null;
    $scope.showDownload = false;
    // For get form data from ng-if content
    $scope.form = {};
    $scope.currentPagePath = null;

    $scope.filtersToggle = false;
    $scope.togglefilters = function() {
        $scope.filtersToggle = !$scope.filtersToggle;
    };

    $scope.globalFiltersToggle = false;
    $scope.globaltogglefilters = function() {
        $scope.globalFiltersToggle = !$scope.globalFiltersToggle;

        if (!$scope.globalFiltersToggle)
        {
            $scope.licenseGlobalSearch = '';
            $scope.getPaginateData();
        }
    };

    $scope.perPage = 10;
    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };

    const url = new URL($location.absUrl());
    const pathAfterDomain = url.pathname.slice(1).split('?')[0].split('#')[0];
    console.log(pathAfterDomain);

    if(pathAfterDomain == 'purchase-report-list'){
        $scope.currentPagePath = 'PURCHASE';
    } else {
        $scope.currentPagePath = 'EXPIRE';

    }
    // Tab switching logic
    $scope.activeTab = $location.search().type || 'PRODUCT';

    // Function to switch tabs
    $scope.switchTab = function(tab) {
      $scope.activeTab = tab;
      $location.search('type', tab);
      pageNumber = '1';
      $scope.getPaginateData(null);
    };

    //Generate License
    $scope.generateLicense = function() {
        var element = angular.element($document[0].getElementById('generateLicense-error-res'));
        element.html('');

        $http({
            url: API_URL + 'license/generate',
            method: 'POST',
            data: {
                product_code: angular.fromJson($scope.productCode),
                license_code: $scope.licenseCode,
                counts: $scope.counts,
                order_source: $scope.orderSource,
                order_reference_no: $scope.orderReferenceNo,
                order_info: $scope.orderInfo,
                order_time: $scope.orderTime,
                email: $scope.email,
                first_name: $scope.firstName,
                last_name: $scope.lastName,
                phone_no: $scope.phoneNo
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#generateLicenseModal .btn-close').click();

            var responseData = response.data.data;
            var generatedLicenseKeyArray = [];
            var generatedLicenseKeyText = '';

            angular.forEach(responseData, function (value, key) {
                // $scope.licenselist.unshift(value);
                // $scope.totalData += 1;
                // $scope.dataTo += 1;

                if (generatedLicenseKeyArray.indexOf(value.license_key) == -1)
                {
                    generatedLicenseKeyArray[key] = value.license_key;
                }
            });

            angular.forEach(generatedLicenseKeyArray, function(value, key) {
                generatedLicenseKeyText += '<div>' + value + '</div>';
            });
            angular.element($document[0].querySelector('#generatedLicenseKeys')).html(generatedLicenseKeyText);
            //$scope.generatedLicenseKeys = generatedLicenseKeyText.trim();
            document.querySelector('#generatedLicenseKeysModalBtn').click();
            $scope.getPaginateData();

        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
            else if (response.status == '500')
            {
                /**** Close Modal ****/
                document.querySelector('#generateLicenseModal .btn-close').click();

                // Show the notification
                notificationAlert('Failure', 'Something went wrong!');
            }
            else
            {
                var errors = response.data.data.error, errorData = '';
                var element = angular.element($document[0].getElementById('generateLicense-error-res'));
                angular.forEach(errors, function (value, key) {
                    errorData += '<div>* ' + value + '</div>';
                });
                element.html(errorData);
            }
        });
    };

    // Timeout function for get data on keypress
    $scope.callPaginateData = function() {
        $timeout.cancel(dataFilterTimeout);
        dataFilterTimeout = $timeout($scope.getPaginateData, 2000);
    };
    $scope.clearDateFilter = function() {
        $scope.expiryFromDateFilter = null;
        $scope.expiryToDateFilter = null;
        $scope.getPaginateData(1);
    }

    $scope.applyDateFilter = function(){

        if(!$scope.expiryFromDateFilter){
            notificationAlert('Failure', 'From date is Required');
            return;
        }
        if(!$scope.expiryToDateFilter){
            notificationAlert('Failure', 'To date is required');
            return;
        }
        if(new Date($scope.expiryFromDateFilter) > new Date($scope.expiryToDateFilter)){
            notificationAlert('Failure', 'From date should be less than to date');
            return;
        }
        $scope.showDownload= true;
        $scope.getPaginateData(1);
    }

    $scope.clearDateFilter = function(type) {
        if(type == 'EXPIRE'){
            $scope.expiryFromDateFilter = null;
            $scope.expiryToDateFilter = null;
        } else {
            $scope.purchaseFromDateFilter = null;
            $scope.purchaseToDateFilter = null;
        }
        $scope.getPaginateData(1);
    }

    $scope.applyDateFilter = function(entityType) {
        function parseDate(dateStr) {
            const [day, month, year] = dateStr.split('-').map(Number); 
            return new Date(year, month - 1, day);
        }
    
        function validateDateRange(fromDateStr, toDateStr) {
            if (!fromDateStr) {
                notificationAlert('Failure', 'From date is Required');
                return false;
            }
            if (!toDateStr) {
                notificationAlert('Failure', 'To date is required');
                return false;
            }
    
            console.log(fromDateStr, toDateStr)
            const fromDate = parseDate(fromDateStr);
            const toDate = parseDate(toDateStr);
            console.log(fromDate, toDate)
            if (fromDate > toDate) {
                notificationAlert('Failure', 'From date should be less than to date');
                return false;
            }
    
            return true;
        }
    
        let isValid;
    
        if (entityType === 'EXPIRE') {
            isValid = validateDateRange($scope.expiryFromDateFilter, $scope.expiryToDateFilter);
        } else {
            isValid = validateDateRange($scope.purchaseFromDateFilter, $scope.purchaseToDateFilter);
        }
    
        if (isValid) {
            $scope.showDownload = true;
            $scope.getPaginateData(1);
        }
    };
    
    

    $scope.downloadExcel = function(entityType) {
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        if(!$scope.statusFilter && $scope.currentPagePath == 'EXPIRE'){
            $scope.statusFilter = 'EXPIRED'
        }
        let payload = {};
        if(entityType == 'EXPIRE'){
            payload = {
                'expiry_from_date': $scope.expiryFromDateFilter,
                'expiry_to_date': $scope.expiryToDateFilter,
                // 'type': $scope.activeTab,
                'status': $scope.statusFilter,
            }
        } else {
            payload = {
                'purchased_from_date': $scope.purchaseFromDateFilter,
                'purchased_to_date': $scope.purchaseToDateFilter,
                'type': $scope.activeTab,
                'status': $scope.statusFilter,
            }
        }
        
        $scope.loading = true;

        $http({
            url: API_URL + 'export/license/userwise',
            method: 'POST',
            data: payload,
            responseType: 'arraybuffer',
        }).then(function success(response) {
            var headers = response.headers();
            var data = response.data;

            var filename = headers['x-filename'] || headers['content-disposition'].replace('attachment; filename=', '');
            var contentType = headers['content-type'];

            var linkElement = document.createElement('a');
            try {
                var blob = new Blob([data], { type: contentType });
                var url = window.URL.createObjectURL(blob);

                linkElement.setAttribute('href', url);
                linkElement.setAttribute("download", filename);

                var clickEvent = new MouseEvent("click", {
                    "view": window,
                    "bubbles": true,
                    "cancelable": false
                });
                linkElement.dispatchEvent(clickEvent);
                $scope.loading = false;
            } catch (ex) {
                $scope.loading = false;
            }

        }, function error(response) {
            $scope.loading = false;
        });
    }

    //Get licenses
    $scope.getPaginateData = function(pageNumber) {
        $scope.licenseFilter = ($scope.licenseFilter) ? $scope.licenseFilter : '';
        $scope.licenseTypeFilter = ($scope.licenseTypeFilter) ? $scope.licenseTypeFilter : '';
        $scope.productNameFilter = ($scope.productNameFilter) ? $scope.productNameFilter : '';
        $scope.macAddressFilter = ($scope.macAddressFilter) ? $scope.macAddressFilter : '';
        $scope.orderReferenceNoFilter = ($scope.orderReferenceNoFilter) ? $scope.orderReferenceNoFilter : '';
        $scope.orderSourceFilter = ($scope.orderSourceFilter) ? $scope.orderSourceFilter : '';
        $scope.emailFilter = ($scope.emailFilter) ? $scope.emailFilter : '';
        $scope.statusFilter = ($scope.statusFilter) ? $scope.statusFilter : '';
        $scope.purchaseFromDateFilter = ($scope.purchaseFromDateFilter) ? $scope.purchaseFromDateFilter : '';
        $scope.purchaseToDateFilter = ($scope.purchaseToDateFilter) ? $scope.purchaseToDateFilter : '';
        $scope.expiryFromDateFilter = ($scope.expiryFromDateFilter) ? $scope.expiryFromDateFilter : '';
        $scope.expiryToDateFilter = ($scope.expiryToDateFilter) ? $scope.expiryToDateFilter : '';
        $scope.licenseGlobalSearch = ($scope.licenseGlobalSearch) ? $scope.licenseGlobalSearch : '';
        $scope.loading = true;


        // if(!$scope.statusFilter && $scope.currentPagePath == 'EXPIRE'){
        //     $scope.statusFilter = 'EXPIRED'
        // }

        if(!pageNumber){
            pageNumber = 1;
        }

        $scope.totalData = 0;
        let currentPage = pageNumber;
        let limit = $scope.perPage;//10;

        // var filters = 'search=' + $scope.licenseGlobalSearch + '&license_key=' + $scope.licenseFilter + '&license_type=' + $scope.licenseTypeFilter + '&product_name=' + $scope.productNameFilter + '&mac_address=' + $scope.macAddressFilter + '&order_reference_no=' + $scope.orderReferenceNoFilter + '&order_source=' + $scope.orderSourceFilter + '&email=' + $scope.emailFilter + '&status=' + $scope.statusFilter + '&purchased_date_from=' + $scope.purchaseFromDateFilter + '&purchased_date_to=' + $scope.purchaseToDateFilter + '&expiry_from_date=' + $scope.expiryFromDateFilter + '&expiry_to_date=' + $scope.expiryToDateFilter;

        let payload = {
            'page_no': pageNumber,
            'limit': limit,
            'search': $scope.licenseGlobalSearch,
            'license_key': $scope.licenseFilter,
            'license_type': $scope.licenseTypeFilter,
            'product_name': $scope.productNameFilter,
            'mac_address': $scope.macAddressFilter,
            'order_reference_no': $scope.orderReferenceNoFilter,
            'order_source': $scope.orderSourceFilter,
            'email': $scope.emailFilter,
            'status': $scope.statusFilter,
            'purchased_from_date': $scope.purchaseFromDateFilter,
            'purchased_to_date': $scope.purchaseToDateFilter,
            'expiry_from_date': $scope.expiryFromDateFilter,
            'expiry_to_date': $scope.expiryToDateFilter,
            'type': $scope.activeTab

        }
        // let url = 'list/active/product/licenses';
        // if($scope.activeTab == 'PACKAGE'){
        let url = 'list/active/package/licenses';
        // }
        $http({
            // url: API_URL + 'get/licenses?' + filters + '&page=' + pageNumber,
            // method: 'GET',
            url: API_URL + url,
            method: 'POST',
            data: payload
        }).then( function success(response) {
            var responseData =  response.data.data;
            // console.log(responseData);
            // $scope.licenselist = responseData.data;
            // $scope.dataFrom = responseData.from;
            // $scope.dataTo = responseData.to;
            // $scope.totalData = responseData.total;
            // $scope.totalPages = responseData.last_page;
            // $scope.currentPage = responseData.current_page;
            // $scope.lastPage  = responseData.last_page;

            $scope.licenselist = responseData.licenses;

            if($scope.licenselist.length > 0){
                $scope.totalData = responseData.total_count;
                $scope.calculateTotalPages($scope.totalData, currentPage, limit);
            }

            $scope.loading = false;

            // Pagination Range
            var pages = [];

            for(var i=1; i <= $scope.totalPages; i++) {
                pages.push(i);
            }

            $scope.range = pages;
        }, function error(response) {
            $scope.loading = false;

            if (response.status == '401')
            {
                window.location.href = '/';
            }
        });
    };


    $scope.calculateTotalPages = function(totalRecords, currentPage, limit) {
        totalRecords = Math.max(1, totalRecords);
        limit = Math.max(1, limit);
        $scope.totalPages = Math.ceil(totalRecords / limit);
        $scope.currentPage = Math.min(Math.max(currentPage, 1), $scope.totalPages);
        $scope.dataFrom = ($scope.currentPage - 1) * limit + 1;
        $scope.dataTo = Math.min($scope.currentPage * limit, totalRecords);
        $scope.lastPage = $scope.totalPages;
        // console.log($scope.lastPage);
    };

    // Manually invoke the function for get data on page load
    $scope.getPaginateData(null, 'PRODUCT');

    // Sort By function
    $scope.sortByField = function(field) {
        $scope.sortBy = field;
        $scope.reverse = !$scope.reverse;
    };

    $scope.perPage = 10;
    $scope.changePerPage = function () {
        $scope.getPaginateData(1);
    };


    //Get Products Codes
    $scope.getProducts = function() {
        $http({
            url: API_URL + 'get/products?status=ACTIVE&page=all&sort_by=product_name&sort_order=ASC',
            method: 'GET',
        }).then( function success(response) {
            $scope.productCodes = response.data.data.data;
        });
    };

    //Get License Codes
    $scope.getLicenseCodes = function() {
        $http({
            url: API_URL + 'get/licenseTypes?status=AVAILABLE&page=all&sort_by=name&sort_order=ASC',
            method: 'GET',
        }).then( function success(response) {
            $scope.licenseCodes = response.data.data.data;
        });
    };

    //Get Packages
    $scope.getPackages = function() {
        $http({
        url: API_URL + 'get/packages?status=AVAILABLE&page=all&sort_by=package_name&sort_order=ASC',
        method: 'GET',
        }).then( function success(response) {
            $scope.packages = response.data.data.data;
        });
    };

    $scope.getCodes = function() {
        $scope.getPackages();
        $scope.getProducts();
        $scope.getLicenseCodes();
    };

    //Get License and Customer Details
    $scope.getlicenseDetails = function(id) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/license-details',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            $scope.licenseDetail = response.data.data;
            $scope.loading = false;
        }, function error(response) {
            $scope.loading = false;
        });
    };

    $scope.getYesterdayDate = function() {
        let currentDate = new Date();
        currentDate.setDate(currentDate.getDate() - 1);

        let date = currentDate.getDate().toString();
        date = (date.length > 1) ? date : '0' + date;

        let month = '0' + (currentDate.getMonth() + 1).toString();
        month = (month.length > 1) ? month : '0' + month;

        let year = currentDate.getFullYear().toString();
        let yesterday = year + '-' + month + '-' + date;

        $scope.yesterdayDate = yesterday.toString();
    };

    $scope.clearFormData = function(formName) {
        var formFields = document.querySelectorAll('form[name = "' + formName + '"] .form-control, form[name="' + formName + '"] [type = "radio"]');

        angular.forEach(formFields, function (formField, key) {
            angular.element(formField).removeClass('ng-empty ng-touched');
        });
        angular.element(document.querySelector('form[name = "' + formName + '"] [id $= "error-res"]')).html('');

        if (formName == 'showLicenseForm')
        {
            $scope.showLicenseForm.$touched = false;
            $scope.showLicenseForm.$untouched = true;
            $scope.showLicenseForm.$dirty = false;
            $scope.showLicenseForm.$pristine = true;

            $scope.showLicenseForm.password.$touched = false;
            $scope.showLicenseForm.password.$untouched = true;
            $scope.showLicenseForm.password.$dirty = false;
            $scope.showLicenseForm.password.$pristine = true;

            $scope.password = '';
        }

        else if (formName == 'deactivateLicenseForm')
        {
            $scope.deactivateLicenseForm.$touched = false;
            $scope.deactivateLicenseForm.$untouched = true;
            $scope.deactivateLicenseForm.$dirty = false;
            $scope.deactivateLicenseForm.$pristine = true;

            $scope.deactivateLicenseForm.deactivateType.$touched = false;
            $scope.deactivateLicenseForm.deactivateType.$untouched = true;
            $scope.deactivateLicenseForm.deactivateType.$dirty = false;
            $scope.deactivateLicenseForm.deactivateType.$pristine = true;

            $scope.deactivateType = '';
        }

        else if (formName == 'activateLicenseForm')
        {
            $scope.activateLicenseForm.$touched = false;
            $scope.activateLicenseForm.$untouched = true;
            $scope.activateLicenseForm.$dirty = false;
            $scope.activateLicenseForm.$pristine = true;

            $scope.activateLicenseForm.activateType.$touched = false;
            $scope.activateLicenseForm.activateType.$untouched = true;
            $scope.activateLicenseForm.activateType.$dirty = false;
            $scope.activateLicenseForm.activateType.$pristine = true;

            $scope.activateType = '';
        }

        else if (formName == 'deleteLicenseForm')
        {
            $scope.deleteLicenseForm.$touched = false;
            $scope.deleteLicenseForm.$untouched = true;
            $scope.deleteLicenseForm.$dirty = false;
            $scope.deleteLicenseForm.$pristine = true;

            $scope.deleteLicenseForm.userPassword.$touched = false;
            $scope.deleteLicenseForm.userPassword.$untouched = true;
            $scope.deleteLicenseForm.userPassword.$dirty = false;
            $scope.deleteLicenseForm.userPassword.$pristine = true;

            $scope.userPassword = '';
            $scope.deleteType = '';
        }

        else if (formName == 'renewForm')
        {
            $scope.renewForm.$touched = false;
            $scope.renewForm.$untouched = true;
            $scope.renewForm.$dirty = false;
            $scope.renewForm.$pristine = true;

            $scope.renewForm.renewLicenseCode.$touched = false;
            $scope.renewForm.renewLicenseCode.$untouched = true;
            $scope.renewForm.renewLicenseCode.$dirty = false;
            $scope.renewForm.renewLicenseCode.$pristine = true;

            $scope.renewForm.renewalType.$touched = false;
            $scope.renewForm.renewalType.$untouched = true;
            $scope.renewForm.renewalType.$dirty = false;
            $scope.renewForm.renewalType.$pristine = true;

            $scope.renewLicenseCode = '';
            $scope.renewalType = '';
        }

        else if (formName == 'generateLicenseForm')
        {
            $scope.generateLicenseForm.$touched = false;
            $scope.generateLicenseForm.$untouched = true;
            $scope.generateLicenseForm.$dirty = false;
            $scope.generateLicenseForm.$pristine = true;

            $scope.generateLicenseForm.productCode.$touched = false;
            $scope.generateLicenseForm.productCode.$untouched = true;
            $scope.generateLicenseForm.productCode.$dirty = false;
            $scope.generateLicenseForm.productCode.$pristine = true;

            $scope.generateLicenseForm.licenseCode.$touched = false;
            $scope.generateLicenseForm.licenseCode.$untouched = true;
            $scope.generateLicenseForm.licenseCode.$dirty = false;
            $scope.generateLicenseForm.licenseCode.$pristine = true;

            $scope.generateLicenseForm.counts.$touched = false;
            $scope.generateLicenseForm.counts.$untouched = true;
            $scope.generateLicenseForm.counts.$dirty = false;
            $scope.generateLicenseForm.counts.$pristine = true;

            $scope.generateLicenseForm.orderSource.$touched = false;
            $scope.generateLicenseForm.orderSource.$untouched = true;
            $scope.generateLicenseForm.orderSource.$dirty = false;
            $scope.generateLicenseForm.orderSource.$pristine = true;

            $scope.generateLicenseForm.orderReferenceNo.$touched = false;
            $scope.generateLicenseForm.orderReferenceNo.$untouched = true;
            $scope.generateLicenseForm.orderReferenceNo.$dirty = false;
            $scope.generateLicenseForm.orderReferenceNo.$pristine = true;

            $scope.generateLicenseForm.orderInfo.$touched = false;
            $scope.generateLicenseForm.orderInfo.$untouched = true;
            $scope.generateLicenseForm.orderInfo.$dirty = false;
            $scope.generateLicenseForm.orderInfo.$pristine = true;

            $scope.generateLicenseForm.orderTime.$touched = false;
            $scope.generateLicenseForm.orderTime.$untouched = true;
            $scope.generateLicenseForm.orderTime.$dirty = false;
            $scope.generateLicenseForm.orderTime.$pristine = true;

            $scope.generateLicenseForm.email.$touched = false;
            $scope.generateLicenseForm.email.$untouched = true;
            $scope.generateLicenseForm.email.$dirty = false;
            $scope.generateLicenseForm.email.$pristine = true;

            $scope.generateLicenseForm.firstName.$touched = false;
            $scope.generateLicenseForm.firstName.$untouched = true;
            $scope.generateLicenseForm.firstName.$dirty = false;
            $scope.generateLicenseForm.firstName.$pristine = true;

            $scope.generateLicenseForm.lastName.$touched = false;
            $scope.generateLicenseForm.lastName.$untouched = true;
            $scope.generateLicenseForm.lastName.$dirty = false;
            $scope.generateLicenseForm.lastName.$pristine = true;

            $scope.generateLicenseForm.phoneNo.$touched = false;
            $scope.generateLicenseForm.phoneNo.$untouched = true;
            $scope.generateLicenseForm.phoneNo.$dirty = false;
            $scope.generateLicenseForm.phoneNo.$pristine = true;

            $scope.productCode = '';
            $scope.licenseCode = '';
            $scope.counts = 1;
            $scope.orderSource = '';
            $scope.orderReferenceNo = '';
            $scope.orderInfo = '';
            $scope.orderTime = '';
            $scope.email = '';
            $scope.firstName = '';
            $scope.lastName = '';
            $scope.phoneNo = '';
        }

        else if (formName == 'resetMacForm')
        {
            $scope.resetMacForm.$touched = false;
            $scope.resetMacForm.$untouched = true;
            $scope.resetMacForm.$dirty = false;
            $scope.resetMacForm.$pristine = true;

            $scope.resetMacForm.resetType.$touched = false;
            $scope.resetMacForm.resetType.$untouched = true;
            $scope.resetMacForm.resetType.$dirty = false;
            $scope.resetMacForm.resetType.$pristine = true;

            $scope.resetType = '';
        }
    };

    //Update License and Customer Details
    $scope.updateCustomerDetails = function() {

        $http({
            url: API_URL + 'license/customer-update',
            method: 'POST',
            data: {
                license_key: $scope.updateLicenseKey,
                mac_address: $scope.updateMacAddress,
                email: $scope.updateEmail,
                first_name: $scope.updateFirstName,
                last_name: $scope.updateLastName,
                phone_no: $scope.updatePhoneNo
            },
        }).then( function success(response) {
            window.location.href = window.location.href;
        }, function error(response) {
            var errors = response.data.data.error, errorData = '';
            var element = angular.element($document[0].getElementById('updateCustomer-error-res'));
            angular.forEach(errors, function (value, key) {
                errorData += '<div>* ' + value + '</div>';
            });
            element.html(errorData);
        });
    };

    $scope.userPasswordToggle = true;
    $scope.toggleUserPassword = function() {
        $scope.userPasswordToggle = !$scope.userPasswordToggle;
    };

    $scope.deleteLicenseKey = function() {
        var element = angular.element($document[0].getElementById('deleteLicenseKey-error-res'));
        element.text('');
        $scope.loading = true;

        $http({
            url: API_URL + 'delete/license',
            method: 'POST',
            data: {
                id : $scope.licenseId,
                password : $scope.userPassword,
                delete_type: ($scope.deleteType) ? $scope.deleteType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>deleted</b> successfully.');

            let responseData = response.data.data;

            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist.splice(index, 1);
                    $scope.totalData -= 1;
                    $scope.dataTo -= 1;
                }
            });

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('deleteLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    $scope.getLicenseHistory = function(id) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/license/history',
            method: 'POST',
            data: {
                id: id
            },
        }).then( function success(response) {
            $scope.licenseHistoryData = response.data.data;
            $scope.loading = false;
        }, function error(response) {

        });
    };

    console.log('kk', $scope.perPage);
    $scope.getLicenseProduct = function(licenseKey) {
        $scope.selectedLicenseKey = licenseKey;
        $scope.getProductByKey(1);
    }

    $scope.getProductByKey = function(pageNumber) {
        $scope.loading = true;
        $http({
            url: API_URL + 'get/licenses?license_key=' + $scope.selectedLicenseKey + '&page='+pageNumber+ '&per_page=' + $scope.perPage,
            method: 'GET',
        }).then( function success(response) {
            // console.log(response);
            let responseData = response.data.data;
            $scope.dataSubFrom = responseData.from;
            $scope.licenseProductData = responseData.data || [];
            $scope.subCurrentPage = responseData.current_page;
            $scope.totalSubPages = responseData.last_page;
            // $scope.licenseProductData = response.data.data || [];
            $scope.loading = false;
        }, function error(response) {
            $scope.loading = false;
        });
    };

    $scope.sortByProductField = function(field) {
        $scope.sortByProduct = field;
        $scope.reverseProduct = !$scope.reverseProduct;
    };


    // Sort By function
    $scope.sortByHistoryField = function(field) {
        $scope.sortByHistory = field;
        $scope.reverseHistory = !$scope.reverseHistory;
    };


    $scope.activateLicense = function() {
        $http({
            url: API_URL + 'license/activate',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                activate_type: ($scope.activateType) ? $scope.activateType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>activated</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });
        }, function error(response) {

        });
    };


    $scope.deactivateLicense = function() {
        $http({
            url: API_URL + 'license/deactivate',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                deactivate_type: ($scope.deactivateType) ? $scope.deactivateType : 'PRODUCT_CODE'
            },
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>deactivated</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });

        }, function error(response) {

        });
    };

    /****  Show License Key Section ****/

    $scope.passwordToggle = true;
    $scope.togglePassword = function() {
        $scope.passwordToggle = !$scope.passwordToggle;
    };

    $scope.getLicenseId = function(license, columnId, alertType) {
        $scope.licenseId = license.license_uuid;
        $scope.licenseData = license;
        $scope.columnId = columnId;
        $scope.alertType = alertType;
        $scope.packageFlag = license.package;
        // console.log(license);
        if(alertType == 'ResetMACAlert'){
            $scope.clearFormData('resetMacForm');
            $scope.resetType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT' : "PACKAGE"
            $scope.resetMacForm.resetType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT' : "PACKAGE"
        }
        if (alertType == 'DeactivateAlert') {
            $scope.clearFormData('deactivateLicenseForm')
            $scope.deactivateType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT_CODE' : "PACKAGE"
        }
        if (alertType == 'ActivateAlert') {
            $scope.clearFormData('activateLicenseForm')
            $scope.activateType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT_CODE' : "PACKAGE"
        }
        if (alertType == 'DeleteAlert') {
            $scope.clearFormData('deleteLicenseForm')
            $scope.deleteType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT_CODE' : "PACKAGE"
        }
    };

    $scope.getLicenseIdSub = function(license, columnId, alertType) {
        $scope.licenseId = license.license_uuid;
        $scope.licenseData = license;
        $scope.columnId = columnId;
        $scope.alertType = alertType;
        $scope.packageFlag = license.package;
    }

    $scope.getRenewData = function(license, product) {
        $scope.renewLicenseId = license.license_uuid;
        $scope.renewLicense = license.hashed_license_key;
        $scope.renewProductCode = product.product_code;
        $scope.renewPackageFlag = license.package;
        $scope.renewLicenseCode = '';
        $scope.renewalType = '';
        $scope.renewalType = $scope.activeTab == 'PRODUCT' ? 'PRODUCT_CODE' : "PACKAGE"
    };


    $scope.copyToClipboard = function (currentElement, targetId)
    {
        var r = document.createRange();
        r.selectNode(document.getElementById(targetId));
        window.getSelection().removeAllRanges();
        window.getSelection().addRange(r);
        document.execCommand('copy');
        window.getSelection().removeAllRanges();
    };

    $scope.renewLicenseDetails = function() {
        var element = angular.element($document[0].getElementById('renewLicense-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'license/renewal',
            method: 'POST',
            data: {
                id: $scope.renewLicenseId,
                license_code: $scope.renewLicenseCode,
                renewal_type: ($scope.renewalType) ? $scope.renewalType : 'PRODUCT_CODE'
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#renewModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'License has been <b>renewed</b> successfully.');

            let responseData = response.data.data;
            angular.forEach(responseData, function (value, key) {
                let index = $scope.licenselist.findIndex(x => x.license_uuid == value.license_uuid);

                if (index > -1)
                {
                    $scope.licenselist[index] = value;
                }
            });

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('renewLicense-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    $scope.getActualLicenseKey = function() {
        var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'get/actual-license',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                password: $scope.password
            }
        }).then( function success(response) {
            var responseData = response.data.data;
            var licenseColumn = angular.element($document[0].getElementById('LicenseKey-field-' + $scope.columnId));
            licenseColumn.text(responseData.license_key);

            angular.element($document[0].querySelector('#LicenseKey-field-' + $scope.columnId + ' ~ i.fa-eye')).remove();
            angular.element($document[0].querySelector('#LicenseKey-field-' + $scope.columnId + ' ~ i.fa-copy')).removeClass('d-none');

            /**** Close Modal ****/
            document.querySelector('#showLicenseKeyModal .btn-close').click();

            $scope.loading = false;

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };

    $scope.getSubActualLicenseKey = function() {
        var element = angular.element($document[0].getElementById('getActualLicenseKey-error-res'));
        element.html('');
        $scope.loading = true;

        $http({
            url: API_URL + 'get/actual-license',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                password: $scope.password
            }
        }).then( function success(response) {
            var responseData = response.data.data;
            var licenseColumn = angular.element($document[0].getElementById('LicenseKey-field-sub-' + $scope.columnId));
            licenseColumn.text(responseData.license_key);

            angular.element($document[0].querySelector('#LicenseKey-field-sub-' + $scope.columnId + ' ~ i.fa-eye')).remove();
            angular.element($document[0].querySelector('#LicenseKey-field-sub-' + $scope.columnId + ' ~ i.fa-copy')).removeClass('d-none');

            /**** Close Modal ****/
            document.querySelector('#showSubLicenseKeyModal .btn-close').click();

            $scope.loading = false;

        }, function error(response) {
            var error = response.data.data.error;
            var element = angular.element($document[0].getElementById('getSubActualLicenseKey-error-res'));
            element.text('* ' + error);
            $scope.loading = false;
        });
    };


    /****  Reset MAC Address ****/

    $scope.resetMACAddress = function() {
        $scope.loading = true;

        $http({
            url: API_URL + 'reset/mac-address',
            method: 'POST',
            data: {
                id: $scope.licenseId
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            document.querySelector('#licenseDetailModal .btn-close').click();
            $scope.loading = false;

            // Show the notification
            notificationAlert('Success', 'MAC Address has been <b>reset</b> successfully.');

            let responseData = response.data.data;
            let index = $scope.licenselist.findIndex(x => x.license_uuid == responseData.license_uuid);

            if (index > -1)
            {
                $scope.licenselist[index] = responseData;
            }

        }, function error(response) {
            var error = response.data.data.error;
            $scope.loading = false;
        });
    };

     /****  Reset MAC Address V2 Api****/

     $scope.resetMAC = function() {
        $scope.loading = true;

        // console.log($scope.licenseId, $scope.resetType);
        // return;

        $http({
            url: API_URL + 'v2/reset/mac-address',
            method: 'POST',
            data: {
                id: $scope.licenseId,
                type:$scope.resetType
            }
        }).then( function success(response) {
            /**** Close Modal ****/
            document.querySelector('#alertModal .btn-close').click();
            document.querySelector('#licenseDetailModal .btn-close').click();
            $scope.loading = false;
            console.log(response);

            // Show the notification
            notificationAlert('Success', 'MAC Address has been <b>reset</b> successfully.');

            let responseData = response.data.data;

            responseData.forEach(responseItem => {
                const existingItemIndex = $scope.licenselist.findIndex(existingItem => existingItem.license_uuid === responseItem.license_uuid);
                if (existingItemIndex !== -1) {
                  $scope.licenselist[existingItemIndex] = { ...$scope.licenselist[existingItemIndex], ...responseItem };
                }
              });

        }, function error(response) {
            // var error = response.data.data.error;
            // console.log(error);
            const { data : { error }, message} = response.data;
            $scope.loading = false;
            notificationAlert('Failure', error[0] || message);
        });
    };

});
