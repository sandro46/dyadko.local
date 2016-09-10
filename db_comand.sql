SELECT count(*) FROM `category` c 
join tmp_cat tc ON c.id=tc.id
WHERE batch_id is null and date_parsed is null


SELECT count(*) FROM `item` i 
join tmp_cat tc ON i.cid=tc.id

SELECT i.* FROM `item` i 
join tmp_cat tc ON i.cid=tc.id
order by i.id desc


update `category` c 
set batch_id = null
WHERE batch_id is not null and date_parsed is null