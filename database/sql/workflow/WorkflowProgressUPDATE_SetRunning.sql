update "WorkflowProgress"
set "Running" = true
where
  "Identifier" = ?
  and "IdentifierType" = ?
  and "WorkflowId" = ?
  and "WorkflowStateId" = ?