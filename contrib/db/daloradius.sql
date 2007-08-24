/*
PostgreSQL schema for daloRADIUS
*/


CREATE TABLE hotspots (
  id BIGSERIAL PRIMARY KEY,
  name varchar(32),
  mac varchar(32),
  geocode varchar(128) 
); 

CREATE TABLE operators (
  id BIGSERIAL PRIMARY KEY,
  username varchar(32),
  password varchar(32) 
);

INSERT INTO operators VALUES (1,'administrator','radius');

CREATE TABLE rates (
  id BIGSERIAL PRIMARY KEY,
  type varchar(32),
  cardbank float,
  rate float 
);


