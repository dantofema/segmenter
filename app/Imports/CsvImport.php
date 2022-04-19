<?php

namespace App\Imports;

use App\Domicilio;
use App\Notifications\ImportHasFailedNotification;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;


class CsvImport implements ToModel,WithHeadingRow, WithBatchInserts, WithChunkReading, ShouldQueue, WithCustomCsvSettings, WithEvents //, WithProgressBar
{
    use Importable,RegistersEventListeners;
    private $rows = 0;
    private $importedBy;


    public function __construct(User $importedBy = null)
    {
        $this->importedBy = $importedBy;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
	 ++$this->rows;
//	die(var_dump($row));
        return new Domicilio([
'prov' => $row['prov'],
'listado_id' => $row['codaglo'] ?? '1',
'nom_provin' => $row['nom_provin'] ?? $row['nom_provincia'],
'dpto' => $row['dpto'],
'nom_dpto' => $row['nom_dpto'],
'codaglo' => $row['codaglo'],
'codloc' => $row['codloc'],
'nom_loc' => $row['nom_loc'],
'codent' => $row['codent'],
'nom_ent' => $row['nom_ent'],
'frac' => $row['frac'],
'radio' => $row['radio'],
'mza' => $row['mza'],
'lado' => $row['lado'],
'nro_inicia' => $row['nro_inicia'],
'nro_final' => $row['nro_final'],
'orden_reco' => $row['orden_reco'] ?? $row['orden_recorrido_viv'],
'nrolist' => $row['nrolist'] ?? $row['nro_listado'],
'ccalle' => $row['ccalle'],
'ncalle' => $row['ncalle'],
'nrocatastr' => $row['nrocatastr'] ?? $row['nrocatastralredef'] ?? $row['nro_catastral'],
'piso' => $row['pisoredef'] ?? $row['piso'],
'casa' => $row['casa'],
'dptohab' => $row['dptohab'] ?? $row['dpto_habitacion'],
'sector' => $row['sector'],
'edificio' => $row['edificio'],
'entrada' => $row['entrada'],
'tipoviv' => $row['tipovivredef'] ?? $row['tipoviv'] ?? null,
'descrip' => $row['descrip'] ?? null,
'descripl' => $row['descripl'] ?? null,
'cpostal' => $row['cpostal'] ?? null,
'ordrecmza' => $row['ordrecmza'] ?? null,
'fechrele' => $row['fechrele'] ?? null,
'tiptarea' => $row['tiptarea'] ?? null
        ]);
    }


    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function getCsvSettings(): array
    {
        return [
            'input_encoding' => 'UTF-8',
            'delimiter' => ','
        ];
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function(ImportFailed $event) {
                $this->importedBy->notify(new ImportHasFailedNotification);
            },
        ];
    }

    public function getRowCount(): int
    {
        return $this->rows;
    }

    public static function afterImport(AfterImport $event)
    {
        //
	echo 'Rows: '.$this->rows;
    }

    /**
     * Get the tags that should be assigned to the job.
     *
     * @return array
     */
    public function tags()
    {	
	if ($this->importedBy){
	        return ['csv', 'user:'.$this->importedBy];
	}
	else{
	        return ['csv', 'sin user'];
	}
    }

}


