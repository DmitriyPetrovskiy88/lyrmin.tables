<?

use \Lyrmin\Tables\ImportTable,
    \Bitrix\Main\Loader,
    \Bitrix\Main\Type,
    \Bitrix\Main\Application;

Loader::includeModule("lyrmin.tables");

class tablesTest extends CBitrixComponent
{
    public function executeComponent()
    {
        $request = Application::getInstance()->getContext()->getRequest();
        //foreach ($request->getPostList() as $key => $value) $r[$key] = $value;

        // delete
        if(!empty($request->getPost("DELETE")) && intval($request->getPost("ELEMENT_ID")) && $request->isPost())
        {
            $this->arResult["DELETE_ELEMENT"] = "Y";
            $r = $this->delete($request->getPost("ELEMENT_ID"));

            if(!is_array($r))
            {
                $this->arResult["RESULT"] = array(
                    "TYPE" => "OK",
                    "MESSAGE" => "Удаление элемента: Элемент удалён",
                    "DATA" => $r
                );
            }
            else
            {
                $this->arResult["RESULT"] = array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => implode("<br />", $r),
                    "DATA" => $r
                );
            }
        }

        // add
        if(!empty($request->getPost("ADD")) && $request->isPost())
        {
            $r = $this->add();

            if(!is_array($r) && is_int($r))
            {
                $this->arResult["RESULT"] = array(
                    "TYPE" => "OK",
                    "MESSAGE" => "Элемент добавлен",
                    "DATA" => $r
                );
            }
            else
            {
                $this->arResult["RESULT"] = array(
                    "TYPE" => "ERROR",
                    "MESSAGE" => implode("<br />", $r),
                    "DATA" => $r
                );
            }
        }

        $this->arResult = array_merge($this->arResult, $this->getList());

        $this->includeComponentTemplate();

    }

    public function getList()
    {
        $rows = array();

        $r = ImportTable::getList(array(
            //'select'  => "*", // имена полей, которые необходимо получить в результате
            //'filter'  => "", // описание фильтра для WHERE и HAVING
            //'group'   => "", // явное указание полей, по которым нужно группировать результат
            //'order'   => "", // параметры сортировки
            //'limit'   => "", // количество записей
            //'offset'  => "", // смещение для limit
            //'runtime' => "" // динамически определенные поля
        ));

        while ($row = $r->fetch())
        {
            $rows["ITEMS"][] = $row;
        }

        return $rows;

        //return "Отработал метод foo() компонента tablesTest";
    }

    protected function add()
    {
        $r = ImportTable::add(array(
            'NAME' => 'Новый элемент',
            //'TITLE' => 'Patterns of Enterprise Application Architecture',
            //'PUBLISH_DATE' => new Type\Date('2002-11-16', 'Y-m-d')
        ));

        if ($r->isSuccess())
        {
            return $r->getId();
        }
        else
        {
            /*
            $errors = $r->getErrors();

            foreach ($errors as $error)
            {
                if ($error->getCode() == 'MY_ISBN_CHECKSUM')
                {
                    // сработал наш валидатор
                }
            }
            */
            return $r->getErrorMessages();
        }
    }

    protected function delete($elementId)
    {
        ImportTable::delete($elementId);

        $r = ImportTable::getList(array(
            //'select'  => "*", // имена полей, которые необходимо получить в результате
            'filter'  => array("ID" => $elementId), // описание фильтра для WHERE и HAVING
            //'group'   => "", // явное указание полей, по которым нужно группировать результат
            //'order'   => "", // параметры сортировки
            //'limit'   => "", // количество записей
            //'offset'  => "", // смещение для limit
            //'runtime' => "" // динамически определенные поля
        ));

        if (!$row = $r->fetch())
        {
            return $elementId;
        }
        else
        {
            return array("Удаление элемента:", "Элемент не удалён");
        }
    }
}