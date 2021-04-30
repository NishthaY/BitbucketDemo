\set db advice2pay

ALTER TABLE "Company" ADD "CompanyEncryptionKey" TEXT NULL;
ALTER TABLE "CompanyParent" ADD "CompanyParentEncryptionKey" TEXT NULL;