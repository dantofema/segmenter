CREATE OR REPLACE FUNCTION indec.cargar_topologia_pais(esquema character varying, tabla character varying)
    RETURNS boolean
    LANGUAGE plpgsql
  AS $function$
    DECLARE 
      var_r record;
      estearc character varying;
      miquery character varying;
      topo_sql character varying;
      topo_name character varying;
      edge_sql character varying;
      srid character varying;
      tolerancia character varying;
      myquery_result character varying;
      id integer;
    BEGIN
      estearc = esquema||'.'||tabla;
      RAISE NOTICE  'Tabla: %', estearc;
      
      CREATE EXTENSION IF NOT EXISTS postgis_topology; 
      srid := 3857;
      tolerancia := 1;
      topo_name := 'pais_topo';
      topo_sql := format('SELECT id FROM topology.topology WHERE name = ''%1$s'';'
                            ,topo_name); 
      EXECUTE topo_sql into id;
          RAISE NOTICE 'SQL % . Con id:%',topo_sql,id;    
        IF (id>0) THEN
          miquery = format('
              -- CREAR TOPOLOGIA
           SELECT topology.TopologySummary(''%1$s'') as myquery_result;'
                               ,topo_name); 
          EXECUTE miquery into myquery_result;
              RAISE NOTICE 'Topologia ya creada';
              -- TODO: Devolver Id, nombre, summary?
              RAISE NOTICE '%', myquery_result;
        ELSE
          miquery = format('
           -- CREAR TOPOLOGIA
              SELECT topology.CreateTopology(''%1$s'',%2$s, %3$s);'
                               , topo_name, srid, tolerancia); 
              RAISE NOTICE 'SQL %',miquery;
          EXECUTE miquery;
        END IF;
      
      --BORRAR VISTAS DE radios, fraccines y tabla arc_topology
      -- TODO: AL BORRAR TABLA arc_topology debería revisar consultar esos 
      -- arcos de la topología para borrarse también.
      miquery = format('DROP VIEW IF EXISTS %1$s.v_radios_pais CASCADE;'
                       ,esquema); 
     RAISE NOTICE 'SQL %',miquery;
      EXECUTE miquery;
      miquery = format('DROP VIEW IF EXISTS %1$s.v_fracciones_pais CASCADE;'
                       ,esquema); 
      RAISE NOTICE 'SQL %',miquery;
      EXECUTE miquery;
      miquery = format('DROP TABLE IF EXISTS %1$s.arc_topology_pais ;'
                       ,esquema); 
      RAISE NOTICE 'SQL %',miquery;
      EXECUTE miquery;
      
      --ARCOS desde topologia.
      edge_sql=format('--ARCOS desde topologia.
                      SELECT topology.ST_AddEdge(
       %1$s,
       wkb_geometry)',topo_name);
       
      -- CARGAR ARCOS y registrar arco junto a info
      miquery = format('-- CARGAR ARCOS y registrar arco junto a info
                       CREATE TABLE %1$s.arc_topology_pais AS (
      SELECT a.*,topology.TopoGeo_AddLineString( ''%2$s'',
       st_transform(wkb_geometry,%4$s),1) edge_id_postgis
      FROM %3$s a);'
                       ,esquema,topo_name,estearc,srid); 
      RAISE NOTICE 'SQL %',miquery;
      EXECUTE miquery;
      
      miquery = format('-- CARGAR ETIQUETAS como Nodos y registrar junto a info
      ALTER TABLE %1$s.lab DROP COLUMN IF EXISTS node_id_postgis_pais ;'
                       ,esquema,estearc); 
      RAISE NOTICE 'SQL %',miquery;
      EXECUTE miquery;
      miquery = format('
      ALTER TABLE %1$s.lab ADD COLUMN node_id_postgis_pais integer;'
                       ,esquema); 
      RAISE NOTICE 'SQL %',miquery;
      EXECUTE miquery;
      miquery = format('
     UPDATE %1$s.lab SET node_id_postgis_pais= topology.AddNode(''%2$s'',
     st_transform(wkb_geometry,%3$s));'
                       ,esquema,topo_name,srid); 
      RAISE NOTICE 'SQL %',miquery;
      EXECUTE miquery;
      
      miquery = format('
      SELECT topology.Polygonize(''%1$s'');'
                      ,topo_name); 
     RAISE NOTICE 'SQL %',miquery;
     EXECUTE miquery;
     
     miquery = format('-- Registrar caras para cada label
     ALTER TABLE %1$s.lab DROP COLUMN IF EXISTS face_id_postgis_pais ;'
                      ,esquema,estearc); 
     RAISE NOTICE 'SQL %',miquery;
     EXECUTE miquery;
     miquery = format('
     ALTER TABLE %1$s.lab ADD COLUMN face_id_postgis_pais integer;'
                      ,esquema,estearc); 
     RAISE NOTICE 'SQL %',miquery;
     EXECUTE miquery;
     miquery = format('
     UPDATE %1$s.lab SET face_id_postgis_pais = topology.GetFaceByPoint(''%2$s'',
      st_transform(wkb_geometry,%3$s),0);'
                      , esquema, topo_name, srid); 
     RAISE NOTICE 'SQL %',miquery;
     EXECUTE miquery;
     
     miquery = format('--CREAR VISTAS DE RADIOS
     CREATE VIEW %1$s.v_radios_pais as (
     SELECT ROW_NUMBER() OVER() gid, prov,depto dpto,codloc,frac,radio, 
     st_union(topology.ST_GetFaceGeometry(''%2$s'',face_id_postgis_pais)) wkb_geometry
     FROM %1$s.lab 
     WHERE lab.face_id_postgis_pais is not null and lab.face_id_postgis_pais!=0
     GROUP BY prov,depto,codloc,frac,radio
     );'
                      ,esquema,topo_name); 
     RAISE NOTICE 'SQL %',miquery;
     EXECUTE miquery;
     
     miquery = format('--CREAR VISTAS DE FRACCIONES
     CREATE VIEW %1$s.v_fracciones_pais as (
     SELECT ROW_NUMBER() OVER() gid, prov,depto dpto,codloc,frac,
     st_union(topology.ST_GetFaceGeometry(''%2$s'',face_id_postgis_pais)) wkb_geometry
     FROM %1$s.lab 
     WHERE lab.face_id_postgis_pais is not null and lab.face_id_postgis_pais!=0
     GROUP BY prov,depto,codloc,frac
     );'
                      ,esquema,topo_name); 
     
     RAISE NOTICE 'SQL %',miquery;
     EXECUTE miquery;
     
     RETURN true ; -- query EXECUTE miquery;
     END 
   $function$
