select
  wp.*
  , ws."Name" as "WorkflowStateName"
  , w."Name" as "WorkflowName"
from
  "WorkflowProgress" wp
  join "WorkflowStateOrder" wo on ( wo."WorkflowId" = wp."WorkflowId" and wo."WorkflowStateId" = wp."WorkflowStateId")
  join "Workflow" w on ( w."Id" = wp."WorkflowId")
  join "WorkflowState" ws on ( ws."Id" = wp."WorkflowStateId" )
WHERE
  wp."Identifier" = ?
  and wp."IdentifierType" = ?
  and w."Id" = ?
ORDER BY
  wo."SortOrder" asc