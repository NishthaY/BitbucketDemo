update "WorkflowProgress"
set
    "Failed" = false
where
    "Identifier" = ?
    and "IdentifierType" = ?
    and "WorkflowId" = ?
    and "WorkflowStateId" = ?