--PostgreSQL
CREATE TABLE public.migrations
(
   id serial primary key, 
   hash character varying(30) NOT NULL, 
   name character varying(100) NOT NULL, 
   updated_at timestamp with time zone DEFAULT NULL, 
   created_at timestamp with time zone DEFAULT NULL
);
COMMENT ON TABLE migrations
  IS 'database migrations';