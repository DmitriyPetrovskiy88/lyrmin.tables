<?
/**
 * Created by PhpStorm.
 * User: las
 * Date: 24/12/2016
 * Time: 23:41
 */

namespace Lyrmin\Tables;

use \Bitrix\Main\Entity,
    \Bitrix\Main\Type,
    \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class ImportTable extends Entity\DataManager
{
    /**
     * Returns DB table name for entity.
     *
     * @return string
     */
    public static function getTableName() // Возвращает название таблицы в БД
    {
        return "b_lyrmin_tables_import";
    }

    /**
     * Returns DB connection name for entity.
     *
     * @return string
     */
    public static function getConnectionName() // Возвращает имя подключения к БД
    {
        return "default";
    }

    /**
     * Returns entity map definition.
     *
     * @return array
     */
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_ID_FIELD'),
            )),
            new Entity\DateField('CREATE_DATE', array(
                'required' => true,
                "default_value" => new Type\DateTime,
                "validation" => function () {
                    return array(
                        new Entity\Validator\Date,
                        function($value, $primary, $row, $field)
                        {
                            /*
                             * $value - значение поля
                             * $primary - массив с первичным ключом, в данном случае [ID = 1]
                             * $row - весь массив данных, переданный в ::add или ::update
                             * $field - объект валидируемого поля - Entity\DateField('CREATE_DATE', ...)
                             */
                            if(true)
                            {
                                return true;
                            }
                            else
                            {
                                return "Не верный формат даты";
                            }
                        }
                    );
                },
                'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_CREATE_DATE_FIELD'),
            )),
            new Entity\DatetimeField('TIMESTAMP_X', array(
                'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_TIMESTAMP_X_FIELD'),
            )),
            new Entity\TextField('DESCRIPTION', array(
                'serialized' => true,
                'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_DESCRIPTION_FIELD'),
            )),
            new Entity\StringField('NAME', array(
                'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_NAME_FIELD'),
            )),
            new Entity\BooleanField('ACTIVE', array(
                'values' => array('N', 'Y'),
                'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_ACTIVE_FIELD'),
            )),
            new Entity\EnumField('DESCRIPTION_TYPE', array(
                'values' => array('html', 'text'),
                'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_DESCRIPTION_TYPE_FIELD'),
            )),
            new Entity\FloatField('QUANTITY', array(
                'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_QUANTITY_FIELD')
            )),
            /*
             * Не работает $arField->getDataType() https://yadi.sk/d/9xPhiPOr36y2zA
             * Решаем с ТП вопрос
            new Entity\ExpressionField('AGE_DAYS', 'DATEDIFF(NOW(), %s)', array('CREATE_DATE'),
                array(
                    "validation" => function () {
                        return array(
                            new Entity\Validator\Date
                        );
                    },
                    'title' => Loc::getMessage('LYRMIN_TABLE_IMPORT_ENTITY_AGE_DAYS_FIELD')
                )
            )
            */
            /*
            'ID' => array(
                'primary' => true,
                'autocomplete' => true,
                'title' => Loc::getMessage('IBLOCK_ENTITY_ID_FIELD'),
            ),
            'TIMESTAMP_X' => array(
                'data_type' => 'datetime',
                'required' => true,
                'title' => Loc::getMessage('IBLOCK_ENTITY_TIMESTAMP_X_FIELD'),
            ),
            'IBLOCK_TYPE_ID' => array(
                'data_type' => 'string',
                'required' => true,
                //'validation' => array(__CLASS__, 'validateIblockTypeId'),
                //'title' => Loc::getMessage('IBLOCK_ENTITY_IBLOCK_TYPE_ID_FIELD'),
            ),
            'IMPORTED' => array(
                'data_type' => 'boolean',
                'values' => array('N', 'Y'),
                //'title' => Loc::getMessage('IBLOCK_ENTITY_ACTIVE_FIELD'),
            ),
            'DESCRIPTION' => array(
                'data_type' => 'text',
                //'title' => Loc::getMessage('IBLOCK_ENTITY_DESCRIPTION_FIELD'),
            ),
            'DESCRIPTION_TYPE' => array(
                'data_type' => 'enum',
                'values' => array('text', 'html'),
                //'title' => Loc::getMessage('IBLOCK_ENTITY_DESCRIPTION_TYPE_FIELD'),
            ),
            */
            /*'PICTURE' => array(
                'data_type' => 'Bitrix\File\File',
                'reference' => array('=this.PICTURE' => 'ref.ID'),
            ),
            'IBLOCK_TYPE' => array(
                'data_type' => 'Bitrix\Iblock\IblockType',
                'reference' => array('=this.IBLOCK_TYPE_ID' => 'ref.ID'),
            ),
            'LID' => array(
                'data_type' => 'Bitrix\Lang\Lang',
                'reference' => array('=this.LID' => 'ref.LID'),
            ),
            'SOCNET_GROUP' => array(
                'data_type' => 'Bitrix\Sonet\SonetGroup',
                'reference' => array('=this.SOCNET_GROUP_ID' => 'ref.ID'),
            ),
            */
        );
    }
}