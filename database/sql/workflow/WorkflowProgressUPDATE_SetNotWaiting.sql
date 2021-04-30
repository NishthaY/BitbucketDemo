update "WorkflowProgress"
set
    "Waiting" = false
where
    "Identifier" = ?
    and "IdentifierType" = ?
    and "WorkflowId" = ?
    and "WorkflowStateId" = ?