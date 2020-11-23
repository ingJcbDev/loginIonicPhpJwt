-- Drop table

-- DROP TABLE public.users;

CREATE TABLE public.users (
	id serial NOT NULL DEFAULT nextval('users_id_seq'::regclass),
	firstname text NULL,
	lastname text NULL,
	username text NULL,
	"password" text NULL,
	created timestamp(0) NULL DEFAULT now()
);
