\set db advice2pay


ALTER TABLE "SupportTimer" DROP COLUMN IF EXISTS "Estimated";
ALTER TABLE "SupportTimer" ADD COLUMN IF NOT EXISTS "ParentTag" text;
