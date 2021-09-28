'use strict';
/** 
  * controllers for Angular File Upload
*/


//app.controller('MyCtrl', [ '$scope', '$http', '$timeout', '$compile', 'Upload', function($scope, $http, $timeout, $compile, Upload) {
//
//    var uploader = $scope.uploader = Upload;
//    $scope.usingFlash = FileAPI && FileAPI.upload != null;
//    $scope.fileReaderSupported = window.FileReader != null && (window.FileAPI == null || FileAPI.html5 != false);
//    uploader.files = [];
//    $scope.$watch('files', function(files) {
//        $scope.formUpload = false;
//        if (files != null) {
//            uploader.progress = 0;
//            for (var i = 0; i < files.length; i++) {
//                uploader.files.push(files[i]);
//                $scope.errorMsg = null;
//                (function(file) {
//                    generateThumbAndUpload(file);
//                })(files[i]);
//            }
//
//        }
//    });
//    uploader.isUploading = true;
//    uploader.isNewFiles = true;
//    $scope.uploadPic = function(file) {
//        $scope.formUpload = true;
//        if (file != null) {
//            $scope.errorMsg = null;
//            uploader.progress = 0;
//            //generateThumbAndUpload(file);
//            uploadUsingUpload(file);
//        }
//    };
//
//    function generateThumbAndUpload(file) {
//        uploader.progress = 0;
//        $scope.errorMsg = null;
//        $scope.generateThumb(file);
//        //uploadUsingUpload(file);
//        //if ($scope.howToSend === 1) {
//        //
//        //} else if ($scope.howToSend == 2) {
//        //    uploadUsing$http(file);
//        //} else {
//        //    uploadS3(file);
//        //}
//    }
//
//    $scope.generateThumb = function(file) {
//        if (file != null) {
//            if ($scope.fileReaderSupported && file.type.indexOf('image') > -1) {
//                $timeout(function() {
//                    var fileReader = new FileReader();
//                    fileReader.readAsDataURL(file);
//                    fileReader.onload = function(e) {
//                        $timeout(function() {
//                            file.dataUrl = e.target.result;
//                        });
//                    }
//                });
//            }
//        }
//    };
//
//    function uploadUsingUpload(file) {
//        file.upload = Upload.upload({
//            url: 'http://yii2-cms.local/admin/test.html' + $scope.getReqParams(),
//            method: 'POST',
//            headers: {
//                'my-header' : 'my-header-value'
//            },
//            fields: {username: $scope.username},
//            file: file,
//            fileFormDataName: 'myFile'
//
//        });
//
//        file.upload.then(function(response) {
//            $timeout(function() {
//                file.result = response.data;
//            });
//        }, function(response) {
//            if (response.status > 0)
//                $scope.errorMsg = response.status + ': ' + response.data;
//        });
//
//        file.upload.progress(function(evt) {
//            // Math.min is to fix IE which reports 200% sometimes
//            file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
//            uploader.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
//        });
//        file.upload.error(function(evt) {
//            file.isError = true;
//        });
//        file.upload.success(function(evt) {
//            file.isSuccess = true;
//        });
//        file.upload.abort(function(evt) {
//            file.isCancel = true;
//        });
//        file.upload.xhr(function(xhr) {
//            // xhr.upload.addEventListener('abort', function(){console.log('abort complete')}, false);
//        });
//    }
//
//    function uploadUsing$http(file) {
//        file.upload = Upload.http({
//            url: 'http://yii2-cms.local/admin/test.html' + $scope.getReqParams(),
//            method: 'POST',
//            headers : {
//                'Content-Type': file.type
//            },
//            data: file
//        });
//
//        file.upload.then(function(response) {
//            file.result = response.data;
//        }, function(response) {
//            if (response.status > 0)
//                $scope.errorMsg = response.status + ': ' + response.data;
//        });
//
//        file.upload.progress(function(evt) {
//            file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
//        });
//    }
//
//    function uploadS3(file) {
//        file.upload = Upload.upload({
//            url : $scope.s3url,
//            method : 'POST',
//            fields : {
//                key : file.name,
//                AWSAccessKeyId : $scope.AWSAccessKeyId,
//                acl : $scope.acl,
//                policy : $scope.policy,
//                signature : $scope.signature,
//                'Content-Type' : file.type === null || file.type === '' ? 'application/octet-stream' : file.type,
//                filename : file.name
//            },
//            file : file
//        });
//
//        file.upload.then(function(response) {
//            $timeout(function() {
//                file.result = response.data;
//            });
//        }, function(response) {
//            if (response.status > 0)
//                $scope.errorMsg = response.status + ': ' + response.data;
//        });
//
//        file.upload.progress(function(evt) {
//            file.progress = Math.min(100, parseInt(100.0 * evt.loaded / evt.total));
//        });
//        storeS3UploadConfigInLocalStore();
//    }
//
//    $scope.generateSignature = function() {
//        $http.post('/s3sign?aws-secret-key=' + encodeURIComponent($scope.AWSSecretKey), $scope.jsonPolicy).
//            success(function(data) {
//                $scope.policy = data.policy;
//                $scope.signature = data.signature;
//            });
//    };
//
//    if (localStorage) {
//        $scope.s3url = localStorage.getItem('s3url');
//        $scope.AWSAccessKeyId = localStorage.getItem('AWSAccessKeyId');
//        $scope.acl = localStorage.getItem('acl');
//        $scope.success_action_redirect = localStorage.getItem('success_action_redirect');
//        $scope.policy = localStorage.getItem('policy');
//        $scope.signature = localStorage.getItem('signature');
//    }
//
//    $scope.success_action_redirect = $scope.success_action_redirect || window.location.protocol + '//' + window.location.host;
//    $scope.jsonPolicy = $scope.jsonPolicy || '{\n  "expiration": "2020-01-01T00:00:00Z",\n  "conditions": [\n    {"bucket": "angular-file-upload"},\n    ["starts-with", "$key", ""],\n    {"acl": "private"},\n    ["starts-with", "$Content-Type", ""],\n    ["starts-with", "$filename", ""],\n    ["content-length-range", 0, 524288000]\n  ]\n}';
//    $scope.acl = $scope.acl || 'private';
//
//    function storeS3UploadConfigInLocalStore() {
//        if ($scope.howToSend === 3 && localStorage) {
//            localStorage.setItem('s3url', $scope.s3url);
//            localStorage.setItem('AWSAccessKeyId', $scope.AWSAccessKeyId);
//            localStorage.setItem('acl', $scope.acl);
//            localStorage.setItem('success_action_redirect', $scope.success_action_redirect);
//            localStorage.setItem('policy', $scope.policy);
//            localStorage.setItem('signature', $scope.signature);
//        }
//    }
//
//    //(function handleDynamicEditingOfScriptsAndHtml($scope) {
//    //    $scope.defaultHtml = document.getElementById('editArea').innerHTML.replace(/\t\t\t\t/g, '');
//    //
//    //    $scope.editHtml = (localStorage && localStorage.getItem('editHtml' + version)) || $scope.defaultHtml;
//    //    function htmlEdit() {
//    //        document.getElementById('editArea').innerHTML = $scope.editHtml;
//    //        $compile(document.getElementById('editArea'))($scope);
//    //        $scope.editHtml && localStorage && localStorage.setItem('editHtml' + version, $scope.editHtml);
//    //        if ($scope.editHtml != $scope.htmlEditor.getValue()) $scope.htmlEditor.setValue($scope.editHtml);
//    //    }
//    //    $scope.$watch('editHtml', htmlEdit);
//    //
//    //    $scope.htmlEditor = CodeMirror(document.getElementById('htmlEdit'), {
//    //        lineNumbers: true, indentUnit: 4,
//    //        mode:  'htmlmixed'
//    //    });
//    //    $scope.htmlEditor.on('change', function() {
//    //        if ($scope.editHtml != $scope.htmlEditor.getValue()) {
//    //            $scope.editHtml = $scope.htmlEditor.getValue();
//    //            htmlEdit();
//    //        }
//    //    });
//    //})($scope, $http);
//
//    $scope.confirm = function() {
//        return confirm('Are you sure? Your local changes will be lost.');
//    };
//
//    $scope.getReqParams = function() {
//        return $scope.generateErrorOnServer ? '?errorCode=' + $scope.serverErrorCode +
//        '&errorMessage=' + $scope.serverErrorMsg : '';
//    };
//    console.info('uploader', uploader);
//    angular.element(window).bind('dragover', function(e) {
//        e.preventDefault();
//    });
//    angular.element(window).bind('drop', function(e) {
//        e.preventDefault();
//    });
//
//    //$timeout(function(){
//    //    $scope.capture = localStorage.getItem('capture'+ version) || 'camera';
//    //    $scope.accept = localStorage.getItem('accept'+ version) || 'image/*';
//    //    $scope.acceptSelect = localStorage.getItem('acceptSelect'+ version) || 'image/*';
//    //    $scope.disabled = localStorage.getItem('disabled'+ version) == 'true' || false;
//    //    $scope.multiple = localStorage.getItem('multiple'+ version) == 'true' || false;
//    //    $scope.allowDir = localStorage.getItem('allowDir'+ version) == 'true' || true;
//    //    $scope.$watch('capture+accept+acceptSelect+disabled+capture+multiple+allowDir', function() {
//    //        localStorage.setItem('capture'+ version, $scope.capture);
//    //        localStorage.setItem('accept'+ version, $scope.accept);
//    //        localStorage.setItem('acceptSelect'+ version, $scope.acceptSelect);
//    //        localStorage.setItem('disabled'+ version, $scope.disabled);
//    //        localStorage.setItem('multiple'+ version, $scope.multiple);
//    //        localStorage.setItem('allowDir'+ version, $scope.allowDir);
//    //    });
//    //});
//
//} ]);


var app = angular.module('app', ['angularFileUpload', 'ngAnimate','angular-loading-bar']);
app.config(['cfpLoadingBarProvider',
    function (cfpLoadingBarProvider) {
        cfpLoadingBarProvider.includeBar = true;
        cfpLoadingBarProvider.includeSpinner = false;

    }]);
app.directive('ngThumb', ['$window', function($window) {
    var helper = {
        support: !!($window.FileReader && $window.CanvasRenderingContext2D),
        isFile: function(item) {
            return angular.isObject(item) && item instanceof $window.File;
        },
        isImage: function(file) {
            var type =  '|' + file.type.slice(file.type.lastIndexOf('/') + 1) + '|';
            return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
        }
    };

    return {
        restrict: 'A',
        template: '<canvas/>',
        link: function(scope, element, attributes) {
            if (!helper.support) return;

            var params = scope.$eval(attributes.ngThumb);

            if (typeof params.file.id !='undefined'){
                var img = $.parseHTML('<img />');
                $(img).attr('src', params.file.url);
                element.html(img);
            }
            if (!helper.isFile(params.file)) return;
            if (!helper.isImage(params.file)) return;

            var canvas = element.find('canvas');
            var reader = new FileReader();

            reader.onload = onLoadFile;
            reader.readAsDataURL(params.file);
            function onLoadFile(event) {
                var img = new Image();
                img.onload = onLoadImage;
                img.src = event.target.result;
            }

            function onLoadImage() {
                var width = params.width || this.width / this.height * params.height;
                var height = params.height || this.height / this.width * params.width;
                canvas.attr({ width: width, height: height });
                canvas[0].getContext('2d').drawImage(this, 0, 0, width, height);
            }
        }
    };
}]);

app.controller('UploadCtrl', ['$scope',  'FileUploader','cfpLoadingBar',
function ($scope, FileUploader,cfpLoadingBar) {

    cfpLoadingBar.start();
    $scope.start_init=function(files,options_drop,filters_add){
        var allFilters = {
            jsFilter: {
                name: 'jsFilter',
                errorMessage: 'Не верный формат файла только JS',
                fn: function (item/*{File|FileLikeObject}*/, options) {
                    var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                    return '|javascript|'.indexOf(type) !== -1;
                }
            },
            cssFilter: {
                name: 'cssFilter',
                errorMessage: 'Не верный формат файла только CSS',
                fn: function (item/*{File|FileLikeObject}*/, options) {
                    var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                    return '|css|'.indexOf(type) !== -1;
                }
            },
            imageFilter:{
                name: 'imageFilter',
                errorMessage: 'Не верный формат файла только изображения',
                fn: function(item /*{File|FileLikeObject}*/, options) {
                    var type = '|' + item.type.slice(item.type.lastIndexOf('/') + 1) + '|';
                    return '|jpg|png|jpeg|bmp|gif|'.indexOf(type) !== -1;
                }
            }
        };
        var uploader = $scope.uploader = new FileUploader(options_drop);
        uploader.errorMessage='';
        if(!angular.isUndefined(files) &&(angular.isObject(files)||angular.isArray(files))){
            angular.forEach(files,function(some) {
                var fileItem = new FileUploader.FileItem(uploader, some, {
                    isSuccess:true,
                    isUploaded:true,
                    progress:0,
                    isCancel:false,
                    isError:false
                });
                uploader.queue.push(fileItem);
            })
        }
        // FILTERS
        if(angular.isDefined(filters_add)&&(angular.isObject(filters_add)||angular.isArray(filters_add))){
            angular.forEach(filters_add,function(some,i) {
                if(angular.isDefined(allFilters[i])){
                    uploader.filters.push(allFilters[i]);
                }

            })
        }
        // CALLBACKS
        uploader.onWhenAddingFileFailed = function (item/*{File|FileLikeObject}*/, filter, options) {
            if(!angular.isUndefined(filter.errorMessage)){
                if(uploader.errorMessage!=''){
                    uploader.errorMessage ="\n"+ filter.errorMessage;
                }else{
                    uploader.errorMessage = filter.errorMessage;
                }

            }

            console.info('onWhenAddingFileFailed', item, filter, options);
        };
        uploader.onAfterAddingFile = function (fileItem) {
            uploader.errorMessage = '';
            console.info('onAfterAddingFile', fileItem);
        };
        uploader.onAfterAddingAll = function (addedFileItems) {
            console.info('onAfterAddingAll', addedFileItems);
        };
        uploader.onBeforeUploadItem = function (item) {
            console.info('onBeforeUploadItem', item);
        };
        uploader.onProgressItem = function (fileItem, progress) {
            console.info('onProgressItem', fileItem, progress);
        };
        uploader.onProgressAll = function (progress) {
            console.info('onProgressAll', progress);
        };
        uploader.onSuccessItem = function (fileItem, response, status, headers) {
            console.info('onSuccessItem', fileItem, response, status, headers);
        };
        uploader.onErrorItem = function (fileItem, response, status, headers) {
            console.info('onErrorItem', fileItem, response, status, headers);
        };
        uploader.onCancelItem = function (fileItem, response, status, headers) {
            console.info('onCancelItem', fileItem, response, status, headers);
        };
        uploader.onCompleteItem = function (fileItem, response, status, headers) {
            console.info('onCompleteItem', fileItem, response, status, headers);
        };
        uploader.onCompleteAll = function () {
            uploader.errorMessage = '';
            console.info('onCompleteAll');
        };

        console.info('uploader', uploader);
    };

    cfpLoadingBar.complete()
}]);