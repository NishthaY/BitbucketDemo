select
  wp.*
  , w."Name" as "WorkflowName"
  , ws."Name" as "WorkflowStateName"
  , ws."NextStateId"
  , ws."Id" as "WorkflowStateId"
from
  "WorkflowProgress" wp
  join "Workflow" w on ( w."Id" = wp."WorkflowId" )
  join "WorkflowState" ws on ( ws."Id" = wp."WorkflowStateId" )
  join "WorkflowStateOrder" wo on ( wo."WorkflowId" = w."Id" and wo."WorkflowStateId" = ws."Id" )
WHERE
  wp."Identifier" = ?
  and wp."IdentifierType" = ?
  and w."Id" = ?
order by wo."SortOrder" desc
limit 1