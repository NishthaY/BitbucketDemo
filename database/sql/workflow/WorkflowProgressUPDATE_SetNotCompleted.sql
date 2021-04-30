update "WorkflowProgress"
set
  "Complete" = false
where
  "Identifier" = ?
  and "IdentifierType" = ?
  and "WorkflowId" = ?
  and "WorkflowStateId" = ?