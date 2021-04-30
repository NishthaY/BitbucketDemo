update "WorkflowProgress"
set "Running" = false
where
  "Identifier" = ?
  and "IdentifierType" = ?
  and "WorkflowId" = ?
  and "WorkflowStateId" = ?