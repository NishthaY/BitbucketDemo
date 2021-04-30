\set db advice2pay

CREATE TABLE "FileTransferProtocol"
(
      "Id" int not null
    , "Protocol" text NOT null
)
WITH (
OIDS=FALSE
);
ALTER TABLE "FileTransferProtocol" OWNER TO :db;
insert into "FileTransferProtocol" ( "Id", "Protocol" ) values ( 1, 'SFTP' );

CREATE TABLE "FileTransfer"
(
    "Id" bigserial NOT NULL,
    "Hostname" text NOT NULL,
    "Username" text NULL,
    "EncryptedPassword" text NULL,
    "EncryptedSSHKey" text NULL,
    "Protocol" text NOT NULL,
    "Port" integer NULL,
    "DestinationPath" text NULL,
    "Enabled" boolean NOT NULL DEFAULT false,
    CONSTRAINT "FileTransferId" PRIMARY KEY ("Id")
)
WITH (
OIDS=FALSE
);
ALTER TABLE "FileTransfer" OWNER TO :db;
delete from "FileTransfer";
ALTER SEQUENCE "FileTransfer_Id_seq" RESTART WITH 1;
insert into "FileTransfer" ("Hostname", "Username", "EncryptedPassword", "EncryptedSSHKey", "Protocol", "Port", "DestinationPath", "Enabled") values ( 'dolio.net', 'sutibo', '{aes-256-cbc}:arIUBHPKPsEYbRc+XdjAMg==:b69234ca86485f5eb93f8ac6fbac936b', '{aes-256-cbc}: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:1680c796fd03d74bfc106c4c01e511a9', 'SFTP', 22, '/home/sutibo/inbox/Advice2Pay/', false );
insert into "FileTransfer" ("Hostname", "Username", "EncryptedPassword", "EncryptedSSHKey", "Protocol", "Port", "DestinationPath", "Enabled") values ( 'optimus.nolasoft.com', 'brian', '{aes-256-cbc}:CMH09ULmUGeuvGKt1pYSPQ==:8b50fdecf96bcfb41b53b2c2b789be09', null, 'SFTP', 22, '/Advice2Pay/development/', false );
insert into "FileTransfer" ("Hostname", "Username", "EncryptedPassword", "EncryptedSSHKey", "Protocol", "Port", "DestinationPath", "Enabled") values ( 'optimus.nolasoft.com', 'brian', '{aes-256-cbc}:CMH09ULmUGeuvGKt1pYSPQ==:8b50fdecf96bcfb41b53b2c2b789be09', null, 'SFTP', 22, '/Advice2Pay/uat/', false );
insert into "FileTransfer" ("Hostname", "Username", "EncryptedPassword", "EncryptedSSHKey", "Protocol", "Port", "DestinationPath", "Enabled") values ( 'optimus.nolasoft.com', 'brian', '{aes-256-cbc}:CMH09ULmUGeuvGKt1pYSPQ==:8b50fdecf96bcfb41b53b2c2b789be09', null, 'SFTP', 22, '/Advice2Pay/sandbox/', false );
insert into "FileTransfer" ("Hostname", "Username", "EncryptedPassword", "EncryptedSSHKey", "Protocol", "Port", "DestinationPath", "Enabled") values ( 'optimus.nolasoft.com', 'brian', '{aes-256-cbc}:CMH09ULmUGeuvGKt1pYSPQ==:8b50fdecf96bcfb41b53b2c2b789be09', null, 'SFTP', 22, '/Advice2Pay/demo/', false );
insert into "FileTransfer" ("Hostname", "Username", "EncryptedPassword", "EncryptedSSHKey", "Protocol", "Port", "DestinationPath", "Enabled") values ( 'optimus.nolasoft.com', 'brian', '{aes-256-cbc}:CMH09ULmUGeuvGKt1pYSPQ==:8b50fdecf96bcfb41b53b2c2b789be09', null, 'SFTP', 22, '/Advice2Pay/prod/', false );


CREATE TABLE "CompanyFileTransfer"
(
    "Id" bigserial NOT NULL,
    "CompanyId" integer NOT NULL,
    "FileTransferId" integer NOT NULL,
    "Enabled" boolean NOT NULL DEFAULT false,
    CONSTRAINT "CompanyFileTransferId" PRIMARY KEY ("Id")
)
WITH (
OIDS=FALSE
);
ALTER TABLE "CompanyFileTransfer" OWNER TO :db;
delete from "CompanyFileTransfer";
ALTER SEQUENCE "CompanyFileTransfer_Id_seq" RESTART WITH 1;
insert into "CompanyFileTransfer" ( "CompanyId", "FileTransferId", "Enabled" ) values ( 2, 1, true );
insert into "CompanyFileTransfer" ( "CompanyId", "FileTransferId", "Enabled" ) values ( 2, 2, true );

