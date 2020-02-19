<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Process;

class Segmentador extends Model
{
    private $resultado=null;
    
    public function segmentar_a_lado_completo($aglo,$dpto,$frac,$radio,$vivs_deseada,$vivs_max,$vivs_min,$mza_indivisible)
	{

        $processLog = Process::fromShellCommandline('echo "Se va a segmentar: $info_segmenta"  >> segmentaciones.log');
        $processLog->run(null, ['info_segmenta' => " Aglomerado: ".$aglo ." Radio ".$radio]);


        $esquema = 'e'.$aglo;


        // Ejemplo: python3 app/developer_docs/segmentacion-core/lados_completos/lados_completos.py e0777.arc 50 084 1 4 20 30 10 1 
        $process = Process::fromShellCommandline('/usr/bin/python3 ./app/developer_docs/segmentacion-core/lados_completos/lados_completos.py $tabla $prov $dpto $frac $radio $deseada $max $min $indivisible'); 
        $process->run(null, ['tabla' => $esquema.".arc",'prov'=>$prov,'dpto'=>$dpto,'frac'=>$frac,'radio'=>$rad,
                             'deseada'=>$vivs_deseada,'max'=>$vivs_max,'min'=>$vivs_min,'indivisible'=>$mza_indivisible)]);
                        // executes after the command finishes
                        if (!$process->isSuccessful()) {
                                dd($process);
                        }else{  
                            $this->resultado=$process->getOutput()
                            dd( $this->resultado );
                        }
            // e0777.arc 50 084 1 4 20 30 10 1');
     }

    public function ver_segmentacion(Radio $radio)
    {
       if (isset($this->resultado)){
            return $this->resultado;
        }else{ return "No hay segmentacion para este radio.";}
    }
}
