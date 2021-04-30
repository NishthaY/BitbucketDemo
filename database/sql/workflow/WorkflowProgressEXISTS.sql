select
  *
from
  "WorkflowProgress"
where
  "Identifier" = ?
  and "IdentifierType" = ?
  and "WorkflowId" = ?
  and "WorkflowStateId" = ?