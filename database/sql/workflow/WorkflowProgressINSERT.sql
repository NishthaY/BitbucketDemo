insert into "WorkflowProgress" ( "Identifier", "IdentifierType", "UserId", "WorkflowId", "WorkflowStateId", "ActionDate" ) values
  (
    ?					  -- identifier
    , ?	        -- identifier type
    , ?					-- user id
    , ?					-- workflow id
    , ?					-- state id
    , NOW()			-- action date
  )