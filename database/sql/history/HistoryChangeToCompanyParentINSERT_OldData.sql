delete from "HistoryChangeToCompanyParent" where "Id" in
(
	select "Id" from
	(
		select "Id", "RowNumber" from
		(
			select
				"Id"
				, ROW_NUMBER() OVER(PARTITION BY "UserId" order by "ChangedToDate" desc) as "RowNumber"
			from
				"HistoryChangeToCompanyParent"
			where
				"UserId" = ?
		) as tbl
		where
		"RowNumber" > ?
	) as tbl2
)
