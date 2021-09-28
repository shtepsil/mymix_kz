<?php
/**
 * @var $this yii\web\View
 */
use shadow\assets\DropzoneAssets;
use shadow\assets\TestAssets;
use yii\helpers\Json;
use yii\helpers\Url;

//DropzoneAssets::register($this);
TestAssets::register($this)
?>
<section id="content">
    <div id="pageEdit">
        <form id="w1" action="/admin/template/save.html" method="post" enctype="multipart/form-data">
            <div style="position: relative;">
                <div class="form-actions panel-footer" style="padding-left: 0px;padding-top: 0px;">
                    <button type="submit" class="btn-success btn-save btn-lg btn" name="continue" data-hotkeys="ctrl+s">
                        <i class="fa fa-retweet"></i> Сохранить
                    </button>
                    &nbsp;&nbsp;
                    <button name="commit" type="submit" class="btn-save-close btn-default hidden-xs btn" data-hotkeys="ctrl+shift+s">
                        <i class="fa fa-check"></i> Сохранить и Закрыть
                    </button>
                    &nbsp;&nbsp;&nbsp;&nbsp;
                    <a href="/admin/template.html" class="btn btn-close btn-sm btn-outline">
                        <i class="fa fa-ban"></i> <span class="hidden-xs hidden-sm">Отмена</span></a>
                </div>
                <ul class="nav nav-tabs tabs-generated">
                    <li id="page-main-panel-li" class="active">
                        <a href="#page-main-panel" data-toggle="tab"><i class="fa fa-suitcase"></i> Основное</a>
                    </li>
                </ul>
            </div>
            <div class="panel form-horizontal">
                <div class="panel-heading">
                    <div class="form-group simple field-template-unique_name required has-success">
                        <label class="col-md-2 control-label" for="template-unique_name">Уникальное название
                            файла</label>

                        <div class="col-md-10">
                            <input type="text" id="template-unique_name" class="form-control" name="Template[unique_name]" placeholder="Уникальное название файла">
                            <p class="help-block help-block-error"></p>
                        </div>
                    </div>
                </div>
                <hr class="no-margin-vr">
                <div class="tab-content no-padding-vr">
                    <div class="tab-pane active" id="page-main-panel">
                        <?php
                            $options_drop=[
                                'url'=>Url::to(['site/upload']),
                                'formData'=>[
                                    ['_csrf'=>Yii::$app->request->getCsrfToken()],
                                    ['alias'=>'js']
                                ]
                            ];
                        ?>
                        <script type="text/javascript">
                            var files_js = [
                                {
                                    name: 'test.jpeg',
                                    size: 44331,
                                    type: 'image/jpeg',
                                    url: 'test.jpeg'
                                }
                            ];
                            var options_drop_js={ url: '<?=Url::to(['site/upload','temp'=>true])?>',formData:[{_csrf:'<?=Yii::$app->request->getCsrfToken()?>'}],alias:'js' }
                        </script>
                        <script type="text/javascript">
                            var files_w3 = [];
                            var options_w3 = {"url":"/admin/upload.html?temp=js","alias":"js","formData":[{"_csrf":"<?=Yii::$app->request->getCsrfToken()?>"}]}</script>
                        <div class="panel-body" >
                            <div class="row ng-scope" ng-controller="UploadCtrl" ng-init="start_init([],{&quot;url&quot;:&quot;/admin/upload.html&quot;,&quot;alias&quot;:&quot;js&quot;,&quot;formData&quot;:[{&quot;_csrf&quot;:&quot;Wm83ZUZMMV8NHHQOBxRXEGIdUAI2KlQUalt6VzY8CAhtDFwuFiJmMw==&quot;}]})" nv-file-drop="" uploader="uploader">

                            <input type="hidden" ng-model="csf"  value="<?=Yii::$app->request->getCsrfToken()?>"/>
                            <div class="col-md-3 has-error" >
                                <h3>Выберите файл</h3>
                                <div ng-show="uploader.isHTML5">
                                    <!-- Example: nv-file-drop="" uploader="{Object}" options="{Object}" filters="{String}" -->
                                    <div nv-file-drop="" uploader="uploader">
                                        <div nv-file-over=""  uploader="uploader" over-class="another-file-over-class" class="well my-drop-zone">
                                            Перенесите файл сюда
                                        </div>
                                    </div>
                                </div>

                                <!-- Example: nv-file-select="" uploader="{Object}" options="{Object}" filters="{String}" -->
                                <span class="btn btn-primary btn-o btn-file margin-bottom-15"> Выбрать файл
                                    <input type="file" nv-file-select="" uploader="uploader" multiple  /><br/>
                                </span>
                                <p class="help-block help-block-error" ng-show="uploader.errorMessage">{{uploader.errorMessage}}</p>
                            </div>

                            <div class="col-md-9" style="margin-bottom: 40px">

                                <h3>Загруженные файлы</h3>
                                <p>Всего файлов: {{ uploader.queue.length }}</p>

                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th width="50%">Название</th>
                                        <th ng-show="uploader.isHTML5">Размер</th>
                                        <th ng-show="uploader.isHTML5">Процесс</th>
                                        <th>Статус</th>
                                        <th>Действия</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr ng-repeat="item in uploader.queue">
                                        <td><strong>{{ item.file.name }}</strong>
                                            <input type="text" name="files[]" value="{{ item.file.url }}" />
                                        </td>
                                        <td ng-show="uploader.isHTML5" nowrap>{{ item.file.size/1024/1024|number:2 }} MB</td>
                                        <td ng-show="uploader.isHTML5">
                                            <div class="progress" style="margin-bottom: 0;" >
                                                <div class="progress-bar" role="progressbar" ng-if="!item.isUploaded"  ng-style="{ 'width':  item.progress + '%' }"></div>
                                                <div class="progress-bar" role="progressbar" ng-if="item.isUploaded"  ng-style="{ 'width':  '100%' }"></div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span ng-show="item.isSuccess"><i class="glyphicon glyphicon-ok"></i></span>
                                            <span ng-show="item.isCancel"><i class="glyphicon glyphicon-ban-circle"></i></span>
                                            <span ng-show="item.isError"><i class="glyphicon glyphicon-remove"></i></span>
                                        </td>
                                        <td nowrap>
                                            <button type="button" class="btn btn-success btn-xs" ng-click="item.upload()" ng-disabled="item.isReady || item.isUploading || item.isSuccess">
                                                <span class="glyphicon glyphicon-upload"></span> Загрузить
                                            </button>
                                            <button type="button" class="btn btn-warning btn-xs" ng-click="item.cancel()" ng-disabled="!item.isUploading">
                                                <span class="glyphicon glyphicon-ban-circle"></span> Отменить
                                            </button>
                                            <button type="button" class="btn btn-danger btn-xs" ng-click="item.remove()">
                                                <span class="glyphicon glyphicon-trash"></span> Удалить
                                            </button>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>

                                <div>
                                    <div>
                                        Процесс загрузки:
                                        <div class="progress" style="">
                                            <div class="progress-bar" role="progressbar" ng-style="{ 'width': uploader.progress + '%' }"></div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success btn-s" ng-click="uploader.uploadAll()" ng-disabled="!uploader.getNotUploadedItems().length">
                                        <span class="glyphicon glyphicon-upload"></span> Загрузить все
                                    </button>
                                    <button type="button" class="btn btn-warning btn-s" ng-click="uploader.cancelAll()" ng-disabled="!uploader.isUploading">
                                        <span class="glyphicon glyphicon-ban-circle"></span> Отменить все
                                    </button>
                                    <button type="button" class="btn btn-danger btn-s" ng-click="uploader.clearQueue()" ng-disabled="!uploader.queue.length">
                                        <span class="glyphicon glyphicon-trash"></span> Удалить все
                                    </button>
                                </div>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>
