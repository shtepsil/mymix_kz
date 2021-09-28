<?php
/**
 * @var $context \frontend\controllers\SiteController
 * @var $this    \yii\web\View
 */

use backend\models\Menu;
use backend\models\MenuCategory;
use backend\modules\catalog\models\Category;
use yii\helpers\Html;
use yii\helpers\Url;

$context = $this->context;
/**
 * @var $menus Menu[]
 */
$menus      = Menu::find()->orderBy(['sort' => SORT_ASC])->where(['isVisible' => 1, 'parent_id' => null])->all();
$mainPhones = explode(',', ($context->city_model->phone) ? $context->city_model->phone : $context->settings->get('main_phone'));
if (is_array($mainPhones)) {
    $mainPhones = array_filter($mainPhones);
} else {
    $mainPhones = [];
}
$countMainPhones = count($mainPhones)-1;
?>
<header class="header">
    <div class="header__position-top">
        <div class="header__position-left-wrapper">
            <div class="select-city" onclick="popup({block_id: '#popupSelCity', action: 'open'});">
                <span><?= $context->city_model->name ?></span>
            </div>
            <?php if (count($context->city_model->ourStores)): ?>
                <a href="<?= Url::to(['site/our-stores', 'id' => $context->city_model->id]) ?>" class="issue-point">
                    <span>
                        <?= \Yii::t('main', '{n, plural,  one{# пункт} few{# пункта} many{# пунктов} other{# пунктов}} выдачи', ['n' => count($context->city_model->ourStores)]) ?>
                    </span>
                </a>
            <?php endif ?>
        </div>
        
        <div class="header__position-right-wrapper">
            <? foreach ($mainPhones as $key => $mainPhone): ?>
                <?= ($key >0)?',&nbsp;':'' ?>
                <a class="header__telephone" href="tel:<?= preg_replace('/[^\d\+]*/', '', $mainPhone) ?>">
                    <span><?= $mainPhone ?></span>
                </a>
            <? endforeach; ?>
        </div>
    </div>
    <div class="header--wrapper">
        <div class="header__position-middle">
            <a href="<?= Url::to(['site/index']) ?>" class="header__logotype display">
                <img src="<?= $context->AppAsset->baseUrl ?>/images/logotype.svg" alt="Магазин полезных товаров" />
            </a>
            <ul class="header__top-menu">
                <?php
                foreach ($menus as $menu) {
                    $options = [];
                    if ($context->activeMenu($menu)) {
                        $options['class'] = 'current';
                    }
                    echo Html::tag('li', Html::a($menu->name, $menu->createUrl()), $options);
                }
                ?>
            </ul>
			<div class="header__position-right-wrapper display">
            <? foreach ($mainPhones as $key => $mainPhone): ?>
                <?= ($key >0)?',&nbsp;':'' ?>
                <a class="header__telephone" href="tel:<?= preg_replace('/[^\d\+]*/', '', $mainPhone) ?>">
                    <span><?= $mainPhone ?></span>
                </a>
            <? endforeach; ?>
       		</div>
            <div class="wrapperOptions header__position-right-wrapper">
                <div class="device-menu__icon" onclick="$('.device-menu__icon').toggleClass('is-active');$('.navMenu').toggleClass('open');"></div>
                <div class="header__feedback" onclick="popup({block_id: '#popupCallback', action: 'open'});">
                    <span></span>
                </div>
                <a href="<?= Url::to(['site/index']) ?>" class="header__logotype">
                <img src="<?= $context->AppAsset->baseUrl ?>/images/logotype.svg" alt="Магазин полезных товаров" />
                </a>
                <?php if (Yii::$app->user->isGuest): ?>
                    <!--                <div class="iconMenu"></div>-->
                    <!--                <div class="topEnter_icon"></div>-->
                    <div class="header__login" onclick="popup({block_id: '#popupEntreg', action: 'open', position_type: 'absolute'});">
                        <span class="far fa-user"></span>
                        <div class="usernameText">Мой профиль</div>
                    </div>
                    <?= $this->render('//blocks/basket') ?>
                <?php else: ?>
                    <!--                <div class="wrapperOptions">-->
                    <!--                    <div class="iconMenu"></div>-->
                    <!--                    <div class="topEnter_icon"></div>-->
                    <div class="header__login">
                        <span class="far fa-user"></span>
                        <div class="usernameText"><?=$context->user->username;?></div>
                    </div>
                    <ul class="cabinetSubmenu">
                        <li>
                            <a href="<?= Url::to(['user/orders']) ?>">Мои заказы</a>
                        </li>
                        <? if ($context->user->isWholesale): ?>
                            <li>
                                <a href="<?= Url::to(['user/wholesale']) ?>">Мой прайс-лист</a>
                            </li>
                        <? endif ?>
                        <li>
                            <a href="<?= Url::to(['user/bonus']) ?>">Бонусы</a>
                        </li>
                        <li>
                            <a href="<?= Url::to(['user/address']) ?>">Мои адреса</a>
                        </li>
                        <li>
                            <a href="<?= Url::to(['user/settings']) ?>">Настройки</a>
                        </li>
                        <li>
                            <a href="<?= Url::to(['site/logout']) ?>">Выйти</a>
                        </li>
                    </ul>
                    <?= $this->render('//blocks/basket') ?>
                    <!--                </div>-->
                <?php endif; ?>
            </div>
        </div>
        <div class="header__position-bottom">
            <nav class="navMenu">
				<div class="header__position-right-wrapper mob">
            	<? foreach ($mainPhones as $key => $mainPhone): ?>
                <?= ($key >0)?',&nbsp;':'' ?>
                <a class="header__telephone" href="tel:<?= preg_replace('/[^\d\+]*/', '', $mainPhone) ?>">
                    <span><?= $mainPhone ?></span>
                </a>
           		<? endforeach; ?>
        		</div>
                <? if (Yii::$app->user->isGuest): ?>
                    <!--                <div class="login_panel">-->
                    <!--                    <span onclick="popup({block_id: '#popupEntreg', action: 'open'});">Войти</span>-->
                    <!--                </div>-->
                <? endif ?>
                <form action="<?= Url::to(['site/search']) ?>" id="form__header__search_display" method="get" class="header__form-search display">
                    <input id="f__header__search__input" type="text" placeholder="Поиск" name="query" autocomplete="off" data-change="search" />
                    <button type="submit"></button>
                    <div class="btn__search__reset" id="btn__search__reset"></div>
                    <div class="__wrapper__search__result" id="wrapper__search__result2">
                        <div class="wrapper__scroll wrapper__scroll__search" id="wrapper__scroll__search2">
                        </div>
                        <a class="btn__red" href="javascript:void(0)" onclick="$('#form__header__search_display').submit()">Смотреть все
                            <span class="search_count"></span>
                        </a>
                    </div>
                </form>
                <form action="<?= Url::to(['site/search']) ?>" method="get" id="form__header__search" class="header__form-search">
                    <input id="f__header__search__input" type="text" placeholder="Поиск" name="query" autocomplete="off" data-change="search" />
                    <button type="submit"></button>
                    <div class="btn__search__reset" id="btn__search__reset"></div>
                    <div class="__wrapper__search__result" id="wrapper__search__result">
                        <div class="wrapper__scroll wrapper__scroll__search" id="wrapper__scroll__search">
                        </div>
                        <a class="btn__red" href="javascript:void(0)" onclick="$('#form__header__search').submit()">Смотреть все
                            <span class="search_count"></span>
                        </a>
                    </div>
                </form>
                <ul class="topMenu">
                    <?php
                    if ($context->mainMenu) {
                        foreach ($context->mainMenu as $key => $cat_menu) {
                            echo '<li class="'.(!empty($cat_menu['submenu']) ? 'dropmenu' : '').'">';
                            if (!empty($cat_menu['submenu'])) {
                                echo '<span>'.$cat_menu['name'].'</span>';
                                echo '<ul class="submenu">
                                        <div class="submenu-li">
                                            <div class="submenu-block-level-1'.($key == 19 ? ' actions' : '').'">';

                                $level2 = '';
                                $level3 = '';
                                $level4 = '';

                                if ($key != 19) {
                                    $level2 .= '<div class="submenu-block-level-2">';
                                    $level2 .= '<div class="submenu-block-level-3">';
                                    $level3 .= '<div class="submenu-block-level-4">';
                                    $level4 .= '<div class="submenu-block-level-5">';
                                }

                                foreach ($cat_menu['submenu'] as $subKey => $sub) {
                                    echo '<div data-id="'.$subKey.'">';

                                    if ($key == 19) {
                                        echo '<img class="menu-img-click" src="'.$sub['img'].'">';
                                    }
                                    else {
                                        $level4 .= '<div data-parent="'.$subKey.'">';

                                        foreach ($sub['banner'] as $b) {
                                            if (!empty($b['img'])) {
                                                $level4 .= '<a href="'.(!empty($b['link']) ? $b['link'] : 'javascript:void(0)').'"><img src="'.$b['img'].'"></a>';
                                            }
                                        }

                                        $level4 .= '</div>';
                                    }

                                    echo '<a href="'.$sub['link'].'">'.$sub['name'].'</a>';

                                    if (!empty($sub['submenu'])) {
                                        $level2 .= '<div data-id="'.$subKey.'">';
                                        $level2 .= '<div><span>'.$sub['name'].'</span></div>
                                                    <ul>';

                                        foreach ($sub['submenu'] as $subSubKey => $subSub) {
                                            $level2 .= '<li data-id="'.$subSubKey.'" class="'.(!empty($subSub['submenu']) ? 'has-parent' : '').'"><a href="'.$subSub['link'].'">'.$subSub['name'].'</a></li>';

                                            if (!empty($subSub['submenu'])) {
                                                $level3 .= '<div data-parent="'.$subSubKey.'">';
                                                $level3 .= '<div><span>'.$subSub['name'].'</span></div>
                                                    <ul>';

                                                foreach ($subSub['submenu'] as $s) {
                                                    $level3 .= '<li><a href="' . $s['link'] . '">' . $s['name'] . '</a></li>';
                                                }

                                                $level3 .= '</ul>
                                                    </div>';

                                            }
                                        }

                                        $level2 .= '</ul>
                                                    </div>';
                                    }

                                    echo '</div>';
                                }

                                if ($key != 19) {
                                    $level2 .= '</div>';
                                    $level3 .= '</div>';
                                    $level2 .= $level3;

                                    $level4 .= '</div>';
                                    $level2 .= $level4;

                                    $level2 .= '</div>';
                                }

                                echo '</div>';
                                echo $level2;
                                echo '</div>
                                      </ul>';

                            }
                            else {
                                echo '<a href="'.$cat_menu['link'].'">'.$cat_menu['name'].'</a>';
                            }

                            echo '</li>';
                        }
                    }
                    ?>
                </ul>
				<!--<ul class="header__top-menu-mob">
				<?php
				foreach ($menus as $menu) {
					$options = [];
					if ($context->activeMenu($menu)) {
						$options['class'] = 'current';
					}
					echo Html::tag('li', Html::a($menu->name, $menu->createUrl()), $options);
				}
				?>
				</ul>-->
            </nav>
        </div>
    </div>
</header>