<?php
/**
 * @author tmtuan
 * created Date: 03-Dec-20
 */
namespace App\Domains\TmtSfObject\Models;

use App\Domains\TmtSfObject\Classes\SObject;
use Illuminate\Database\Eloquent\Model;
use Schema;
use Cache;

class SalesforceModel extends Model {
    public static function tmtAutoCloneSalesforceSchema(&$table, $currTbl, $sfTbl)
    {
        $objectDescribeResult = SObject::describe($sfTbl);
            if ($objectDescribeResult) {
            foreach ($objectDescribeResult['fields'] as $field) {
                if (Schema::hasColumns($currTbl, [$field['name']])) {
                    continue;
                }

                switch ($field['type']) {
                    case 'id':
                    case 'reference':
                        $table->string($field['name'], $field['length'])->nullable();
                        if ($field['name'] == 'Id') {
                            $table->primary(['Id']);
                        }
                        break;
                    case 'address':
                        $table->text($field['name'])->nullable();
                        break;
                    case 'string':
                    case 'picklist':
                    case 'phone':
                    case 'url':
                    case 'email':
//                        if ($field['length'] > 255) {
                        $table->text($field['name'])->nullable();
//                        } else {
//                            $table->string($field['name'], $field['length'])->nullable();
//                        }
                        break;
                    case 'textarea':
                        $table->text($field['name'], $field['length'])->nullable();
                        break;
                    case 'boolean':
                        $table->boolean($field['name'])->nullable();
                        break;
                    case 'double':
                    case 'currency':
                        $table->double($field['name'], $field['precision'], $field['scale'])->nullable();
                        break;
                    case 'int':
                        $table->integer($field['name'])->nullable();
                        break;
                    case 'datetime':
                        $table->dateTime($field['name'])->nullable();
                        break;
                    case 'date':
                        $table->date($field['name'])->nullable();
                        break;
                    default:
                        if(isset($field['length']) && $field['length'] > 0){
                            $table->text($field['name'], $field['length'])->nullable();
                        } else {
                            $table->text($field['name'])->nullable();
                        }
                        break;
                }
            }
        }
    }
}
