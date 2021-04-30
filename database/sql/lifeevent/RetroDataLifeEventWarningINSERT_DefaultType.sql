insert into "RetroDataLifeEventWarning" ( "CompanyId", "ImportDate", "ImportDataId", "IssueType", "Issue")
select
    e."CompanyId"
    , e."ImportDate"
    , rd."ImportDataId"
    , 'default'
    , case
        when ? = 'ignore' then 'Default Selected: The coverage start date change was due to a life event update.  Coverage has been in effect and no adjustments prior to the new start date are necessary.'
        when ? = 'retro' then 'Default Selected: The coverage start date change was due to a correction made in the source system. An adjustment may be applied.'
        else 'Default Selected: A coverage start date change has been made for this life.'
    end as "Message"
from
    "RetroDataLifeEvent" e
    join "RetroData" rd on ( rd."Id" = e."RetroDataId")
    join "ImportData" d on ( d."Id" = rd."ImportDataId")
where
    e."CompanyId" = ?
    and e."ImportDate" = ?
    and e."DefaultType" is not null