--
-- PostgreSQL database dump
--

-- Dumped from database version 9.5.16
-- Dumped by pg_dump version 9.5.16

SET statement_timeout = 0;
SET lock_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', 'public' , false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: gobierno_local_departamento; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.gobierno_local_departamento (
    gobierno_local_id integer NOT NULL,
    departamento_id integer NOT NULL
);


--
-- PostgreSQL database dump complete
--

