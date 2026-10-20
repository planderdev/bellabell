-- BELLABELL FARM contact storage. Apply once to the PostgreSQL database named by DATABASE_URL.
-- The application connects with a role that owns these tables; no anonymous/API access is needed.

create table if not exists contact_attempts (
    id         bigserial primary key,
    ip_hash    text        not null,
    nonce      text        unique,
    created_at timestamptz not null default now()
);
create index if not exists contact_attempts_ip_time on contact_attempts (ip_hash, created_at desc);

create table if not exists contact_enquiries (
    id         bigserial primary key,
    locale     text        not null,
    name       text        not null,
    company    text        not null default '',
    email      text        not null,
    type       text        not null check (type in ('distribution','partnership','global','other')),
    message    text        not null,
    ip_hash    text,
    user_agent text,
    mail_sent  boolean     not null default false,
    handled_at timestamptz,
    created_at timestamptz not null default now()
);
create index if not exists contact_enquiries_created on contact_enquiries (created_at desc);

-- Lock both tables down for Supabase's anon/authenticated API roles. The PHP app uses the direct Postgres role.
alter table contact_attempts  enable row level security;
alter table contact_enquiries enable row level security;
