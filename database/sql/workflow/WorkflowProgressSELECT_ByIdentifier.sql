select
       wp.*
        , w."Name" as "WorkflowName"
        , ws."Name" as "WorkflowStateName"
from
     "WorkflowProgress" wp
    join "Workflow" w on ( w."Id" = wp."WorkflowId" )
    join "WorkflowState" ws on ( ws."Id" = wp."WorkflowStateId" )
    join "WorkflowStateOrder" wso on ( wso."WorkflowStateId" = ws."Id" )
where
    wp."Identifier" = ?
    and wp."IdentifierType" = ?
order by wso."SortOrder" asc