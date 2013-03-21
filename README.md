kohana-coolmigrations
=====================

Rails inspired DB Migration module for kohana (native support postgresql &amp; mysql)

Based on migrations-flexible(https://github.com/ferpetrelli/kohana-flexible-migrations).

## Depending:

* Minion tasks - CLI (Kohana module)
* PostgreSQL database support if you need it (https://github.com/rainweezy/kohana-databasex)

## List of command (console):

* php index.php db:migrate (--db=database_name --step=num|all) // Default: run all pending migrations
* php index.php db:rollback (--db=database_name --step=num|all) // Default: rollback last executed migration
* php index.php generate:migration --name=migration_name

--db - name of database
    - 'database_name' is name of database from database config.
--step - amount of migrations to be running
    - 'all' - word, that mean 'run all migrations'
--name - name for new migration

## Migration functions

All possible methods are:

up     - migrate
down   - rollback
cahnge - migrate/rollback if possible

All possible functions are:

create_table
drop_table
rename_table

add_column
rename_column
change_column(not work!!!)
remove_column

add_index
remove_index

belongs_to(not work!!!)
has_one(not work!!!)
has_one_trough(not work!!!)
has_many_trough(not work!!!)
has_many(not work!!!)

run_query

Possible DB columns datatypes are

binary
boolean
date
datetime
decimal
float
integer
primary_key
string
text
time
timestamp