--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.10
-- Dumped by pg_dump version 9.6.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: _services; Type: TABLE; Schema: public; Owner: rebasedata
--

CREATE TABLE public._services (
    id smallint,
    name character varying(25) DEFAULT NULL::character varying,
    color character varying(1) DEFAULT NULL::character varying,
    base_price numeric(7,2) DEFAULT NULL::numeric,
    allow_custom_price smallint,
    description character varying(15) DEFAULT NULL::character varying,
    created_at character varying(19) DEFAULT NULL::character varying,
    updated_at character varying(19) DEFAULT NULL::character varying
);


ALTER TABLE public._services OWNER TO rebasedata;

--
-- Data for Name: _services; Type: TABLE DATA; Schema: public; Owner: rebasedata
--

COPY public._services (id, name, color, base_price, allow_custom_price, description, created_at, updated_at) FROM stdin;
1	Braces		40000.00	1	Depende sa Case	2025-12-14 11:41:57	2025-12-17 10:05:17
2	Extraction		900.00	1		2025-12-15 06:33:44	2025-12-15 06:33:44
3	Filling		800.00	1		2025-12-15 06:34:04	2025-12-15 06:34:04
4	Pasta Front Teeth		1000.00	1		2025-12-15 06:34:25	2025-12-15 06:34:25
5	Cleaning/Oral Prophylaxis		800.00	1		2025-12-15 06:34:38	2025-12-15 06:34:38
6	Denture/Pustiso		3000.00	1		2025-12-15 06:34:51	2025-12-15 06:34:51
7	Retainers		5000.00	1		2025-12-15 06:36:01	2025-12-15 06:36:01
8	Teeth Whitening		6000.00	1		2025-12-15 06:36:16	2025-12-15 06:36:16
9	Consultation		300.00	1		2025-12-15 06:36:27	2025-12-15 06:36:27
10	Root Canal Therapy		8000.00	1		2025-12-15 06:36:38	2025-12-15 06:36:38
11	Impacted Oral Surgery		8000.00	1		2025-12-15 06:36:50	2025-12-15 06:36:50
12	Composite Veneers		2500.00	1		2025-12-15 06:37:02	2025-12-15 06:37:02
13	Indirect Veneers		8000.00	1		2025-12-15 06:37:12	2025-12-15 06:37:12
14	Checkup		300.00	1		2025-12-15 06:37:23	2025-12-15 06:37:23
15	X-Ray		600.00	1		2025-12-15 06:37:36	2025-12-15 06:37:36
16	Severe Case		2000.00	1		2025-12-15 06:37:53	2025-12-15 06:37:53
17	Medical Certificate		300.00	1		2025-12-17 11:30:47	2025-12-17 11:30:47
18	Ambot		12313.00	0		2025-12-23 19:08:17	2025-12-23 19:08:17
\.


--
-- PostgreSQL database dump complete
--

