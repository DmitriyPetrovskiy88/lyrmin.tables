<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
use \Bitrix\Main\Application;

$request = Application::getInstance()->getContext()->getRequest();
?>
<div class="container">
    <br />
    <div class="row">
        <form action="<?=POST_FORM_ACTION_URI?>" method="post" class="form-horizontal">
            <div class="col-lg-2">
                <button type="submit" name="ELEMENT_ADD" id="ELEMENT_ADD" value="ELEMENT_ADD" class="btn btn-default btn-block">Добавить элемент</button><br />
            </div>
            <div class="col-lg-3">
                <select class="form-control">
                    <option>Список элементов</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                </select>
            </div>
            <div class="col-lg-3">
                <button type="submit" name="ELEMENT_DEL" id="ELEMENT_DEL" value="ELEMENT_DEL" class="btn btn-danger">Удалить элемент</button>
            </div>
        </form>
    </div>
    <br />
    <pre>
        <?
        print_r($request);
        print_r($arResult);
        ?>
    </pre>
</div>
