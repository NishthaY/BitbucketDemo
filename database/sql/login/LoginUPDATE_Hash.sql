update "Login" set "TwoFactorHash"=?, "TwoFactorExpiration"=current_timestamp + (2 * interval '1 minute') where "UserId"=?