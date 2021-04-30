\set db advice2pay

-- Add the LostLife column to the ReportTransamericaCommission table.
-- This will allow us to track lives that drop off between the current and previous months.
alter table "ReportTransamericaCommission" add "LostLife" boolean default false not null;