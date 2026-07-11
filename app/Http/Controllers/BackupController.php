<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackupController extends Controller
{
    public function backupDB(Request $request)
    {
        $connection = $request->input("connection");
        config(["database.default" => $connection]);
        $mysqlHostName      = env('DB_HOST');
        $mysqlUserName      = env('DB_USERNAME');
        $mysqlPassword      = env('DB_PASSWORD');
        $DbName             = env('DB_DATABASE');
        $tables             = array();
        $connect = new \PDO("mysql:host=$mysqlHostName;dbname=$DbName;charset=utf8", "$mysqlUserName", "$mysqlPassword", array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        $get_all_table_query = "SHOW TABLES";
        $statement = $connect->prepare($get_all_table_query);
        $statement->execute();
        $result = $statement->fetchAll();

        $prep = "Tables_in_$DbName";
        foreach ($result as $res) {
            $tables[] =  $res[$prep];
        }

        $output = '';
        $alterStatements = [];
        foreach ($tables as $table) {
            $show_table_query = "SHOW CREATE TABLE " . $table . "";
            $statement = $connect->prepare($show_table_query);
            $statement->execute();

            $show_table_result = $statement->fetchAll();
            //detached CONSTRAINT
            foreach ($show_table_result as $show_table_row) {
                $preg = 'CONSTRAINT `(.*?)` FOREIGN KEY \(`(.*?)`\) REFERENCES `(.*?)` \(`(.*?)`\)';
                preg_match_all('/' . $preg . '/', $show_table_row["Create Table"], $matches, PREG_SET_ORDER);
                $createTableWithoutConstraints = preg_replace('/,?\s*' . $preg . ',?/', '', $show_table_row["Create Table"]);
                if ($matches) {
                    $alterTableQuery = "ALTER TABLE `$table` ";
                    foreach ($matches as $match) {
                        $constraintName = $match[1];
                        $columnName = $match[2];
                        $referencedTable = $match[3];
                        $referencedColumn = $match[4];

                        $alterTableQuery .= "ADD CONSTRAINT `$constraintName` FOREIGN KEY (`$columnName`) REFERENCES `$referencedTable` (`$referencedColumn`), ";
                    }
                    $alterStatements[] = trim($alterTableQuery, ', ') . ';COMMIT;';
                }

                $output .= "\n\n" . $createTableWithoutConstraints . ";\n\n";
            }

            $select_query = "SELECT * FROM " . $table . "";
            $statement = $connect->prepare($select_query);
            $statement->execute();
            $total_row = $statement->rowCount();
            if (!$total_row) {
                continue;
            }
            $columns = [];

            for ($count = 0; $count < $statement->columnCount(); $count++) {
                $column = $statement->getColumnMeta($count);
                $columns[] = "`" . $column['name'] . "`";
            }
            $values = [];
            $output .= "\nINSERT INTO $table (";
            $output .= "" . implode(", ", $columns) . ") VALUES (\n";
            for ($count = 0; $count < $total_row; $count++) {
                $single_result = $statement->fetch(\PDO::FETCH_ASSOC);
                $table_value_array = array_values($single_result);
                $rowValues = [];

                foreach ($table_value_array as $value) {
                    if ($value === null) {
                        $rowValues[] = "NULL";
                    } elseif (is_numeric($value)) {
                        $rowValues[] = $value;
                    } elseif (is_array($value) || is_object($value)) {
                        $jsonValue = json_encode($value);
                        $rowValues[] = "'" . addslashes($jsonValue) . "'";
                    } else {
                        $rowValues[] = "'" . addslashes($value) . "'";
                    }
                }

                $values[] = "(" . implode(", ", $rowValues) . ")";
            }
            $output .= implode(",\n ", $values) . ");\n";
        }

        $file_name = "smd_db_$connection" . date('y-m-d') .' '.time(). '.sql';

        $file_handle = fopen($file_name, 'w+');
        fwrite($file_handle, $output);
        //add CONSTRAINT foreign key 

        foreach ($alterStatements as $alterStatement) {
            fwrite($file_handle, $alterStatement . "\n");
        }
        fclose($file_handle);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . basename($file_name));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_name));

        ob_clean();
        flush();

        readfile($file_name);
        unlink($file_name);
        
    }
}
