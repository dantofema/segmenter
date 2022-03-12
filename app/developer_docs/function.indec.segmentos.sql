/*
genera estadísticas de cantidad de segmentos
y radios
autor: -h+m
fecha: 2022-01-17 Lu
fecha: 2022-03-11 Vi
*/

DROP FUNCTION if exists indec.segmentos();
create or replace function indec.segmentos()
 returns table (
 esquema text, prov integer, dpto integer, codloc integer, frac integer, radio integer, 
 seg text, segmento_id bigint, viviendas numeric 
)
language plpgsql volatile
set client_min_messages = 'notice'
as $function$
declare
strSQL text;
rec record;
begin
RAISE NOTICE 'Buscando localidades con r3...';
 FOR rec IN SELECT table_schema, table_name
            FROM information_schema.tables
            WHERE table_schema like 'e________' and table_name = 'r3'
 LOOP
     strSQL := CONCAT_WS(' union ',strSQL,'select ''' || rec.table_schema || ''' esquema, 
                                 prov::integer, dpto::integer, codloc::integer, 
                                 frac::integer, radio::integer, seg, segmento_id, viviendas from ' || 
               rec.table_schema || '.' || rec.table_name);
 END LOOP;

RAISE NOTICE 'Consulta armada';
return query
EXECUTE strSQL;

-- usando la idea de
-- https://stackoverflow.com/questions/35486057/dynamic-union-all-query-in-postgres

end
$function$

