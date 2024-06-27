<?php
/**
 * @author tmtuan
 * created Date: 04-Jun-21
 */
namespace App\Domains\Acp\Http\Controllers\Backend\Import;

use App\Domains\Auth\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CsvFiles extends Controller {
    public function ImportCsv() {
        return view('backend.manage.importCsv');
    }

    public function ImportCsvAction(Request $request){
        $data = Excel::import(new AccountImport(), $request->file('file')->store('temp'));

        dd($data);
    }

    /**
     * Import 300 account and change password for demo purpose
     * @return mixed
     */
    public function importAccount() {
        return view('backend.manage.importAccount');
    }

    public function ImportAccountAction(Request $request){
        $path = $request->file('file');

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = [];
        foreach ($worksheet->getRowIterator() AS $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(FALSE); // This loops through all cells,
            $cells = [];
            foreach ($cellIterator as $cell) {
                $cells[] = $cell->getValue();
            }
            $rows[] = $cells[0];
        }

        //update password
        $result = [];
        foreach ($rows as $item) {
            $user = User::where('username', $item)->first();

            if ( isset($user->id) ) {
                $user->password_changed_at = now();
                $user->password = '@ctg123@';
                $user->force_pass_reset = 1;
                tap($user)->update();
                $result[] = [
                    $user->username,
                    'Update password success'
                ];
            } else {
                $result[] = [
                    $item,
                    'No Account Found!'
                ];
            }
        }
        return view('backend.manage.importAccount', ['result' => $result]);
    }
}
