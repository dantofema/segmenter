/*

crea una tabla con los srids declarados de todos los esquemas
según la tabla ePPDDDLLL.arc
para encontrar los que fueron declarados en otra faja que no era la que correspondía

autor: -h+m
fecha: 2022-07-16 Ma
*/


DROP FUNCTION if exists indec.srids();
create or replace function indec.srids()
   returns table (
    esquema text,
    srid integer
)
language plpgsql volatile
set client_min_messages = 'notice'
as $function$
declare
strSQL text;
rec record;
count_loc integer;
begin
count_loc := 0;
RAISE NOTICE 'Buscando tabla de arc de todas las localidades...';
 FOR rec IN SELECT table_schema
            FROM information_schema.tables
            WHERE table_schema like 'e________' and table_name in ('arc')
            GROUP BY table_schema
            HAVING count(*) = 1
 LOOP
     strSQL := CONCAT_WS(' union ',strSQL,'
select distinct ''' || rec.table_schema::text || ''' as esquema, st_srid(wkb_geometry) as srid
from ' || rec.table_schema || '.arc'
);
    count_loc := count_loc + 1;
 END LOOP;

RAISE NOTICE 'Juntando % srids', count_loc;
return query

EXECUTE strSQL;

end
$function$



 
