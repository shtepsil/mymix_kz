<?php
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JqueryAsset;

/* @var $this \yii\web\View */
/* @var $url string */
/* @var $enforceRedirect boolean */
$url = Url::to(['site/index']);
$enforceRedirect = true;
?>
<!DOCTYPE html>
<html>
<head>
    <script>
        function popupWindowRedirect(url, enforceRedirect) {
            if (window.opener && !window.opener.closed) {
                if (enforceRedirect === undefined || enforceRedirect) {
//                    window.opener.location = url;
                }
//                var script = document.createElement("script");
//                script.innerHTML = "onstorage()";
//                window.opener.document.body.appendChild(script);
                window.opener.eval(' onstorage(<?=Json::encode($options)?>) ');
                window.opener.focus();
                window.close();
            } else {
                window.location = url;
            }
        }
        popupWindowRedirect(<?= Json::encode($url) ?>, <?= Json::encode($enforceRedirect) ?> );
    </script>
</head>
<body>
<h2 id="title" style="display:none;">Redirecting back to the &quot;<?= Yii::$app->name ?>&quot;...</h2>
<h3 id="link">
    <a href="<?= $url ?>">Click here to return to the &quot;<?= Yii::$app->name ?>&quot;.</a>
</h3>
<script type="text/javascript">
    document.getElementById('title').style.display = '';
    document.getElementById('link').style.display = 'none';
</script>
</body>
</html>
