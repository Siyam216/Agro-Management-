USE agro_mgmt;

-- ---------- 1) Utility FUNCTION: age in days ----------
DROP FUNCTION IF EXISTS fn_animal_age_days;
DELIMITER //
CREATE FUNCTION fn_animal_age_days(p_animal_id INT)
RETURNS INT DETERMINISTIC
BEGIN
  DECLARE d DATE;
  SELECT date_of_birth INTO d FROM animals WHERE animal_id = p_animal_id;
  IF d IS NULL THEN
    RETURN NULL;
  END IF;
  RETURN TIMESTAMPDIFF(DAY, d, CURDATE());
END//
DELIMITER ;

-- ---------- 2) PROCEDURE: transfer an animal between farmers (with checks) ----------
DROP PROCEDURE IF EXISTS sp_transfer_animal;
DELIMITER //
CREATE PROCEDURE sp_transfer_animal(IN p_animal_id INT, IN p_from_farmer INT, IN p_to_farmer INT)
BEGIN
  DECLARE v_owner INT;

  IF p_from_farmer = p_to_farmer THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Source and destination farmers must differ';
  END IF;

  SELECT farmer_id INTO v_owner FROM animals WHERE animal_id = p_animal_id FOR UPDATE;
  IF v_owner IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Animal not found';
  END IF;
  IF v_owner <> p_from_farmer THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Ownership mismatch';
  END IF;

  UPDATE animals SET farmer_id = p_to_farmer WHERE animal_id = p_animal_id;
END//
DELIMITER ;

-- ---------- 3) PROCEDURE: daily totals between dates ----------
DROP PROCEDURE IF EXISTS sp_daily_totals;
DELIMITER //
CREATE PROCEDURE sp_daily_totals(IN p_from DATE, IN p_to DATE)
BEGIN
  SELECT DATE(prod_date) AS prod_date,
         SUM(milk_liters) AS sum_milk,
         SUM(eggs_count)  AS sum_eggs,
         SUM(weight_gain_kg) AS sum_weight
  FROM production
  WHERE prod_date BETWEEN p_from AND p_to
  GROUP BY DATE(prod_date)
  ORDER BY prod_date;
END//
DELIMITER ;

-- ---------- 4) TRIGGER: enforce species-specific production ----------
DROP TRIGGER IF EXISTS trg_prod_species_check;
DELIMITER //
CREATE TRIGGER trg_prod_species_check
BEFORE INSERT ON production
FOR EACH ROW
BEGIN
  DECLARE v_species ENUM('Cattle','Goat','Sheep','Poultry','Duck');
  SELECT species INTO v_species FROM animals WHERE animal_id = NEW.animal_id;
  IF v_species IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Animal not found';
  END IF;
  -- Eggs only for Poultry/Duck
  IF NEW.eggs_count > 0 AND v_species NOT IN ('Poultry','Duck') THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Non-avian species cannot lay eggs';
  END IF;
END//
DELIMITER ;

-- ---------- 5) VIEW with WINDOW FUNCTIONS: top producers & 7-day moving avg ----------
DROP VIEW IF EXISTS v_production_stats;
CREATE VIEW v_production_stats AS
SELECT
  a.animal_id,
  a.tag_code,
  a.species,
  p.prod_date,
  p.milk_liters,
  p.eggs_count,
  SUM(p.milk_liters) OVER (PARTITION BY a.animal_id ORDER BY p.prod_date
    ROWS BETWEEN 6 PRECEDING AND CURRENT ROW) AS milk_7d_sum,
  AVG(p.milk_liters) OVER (PARTITION BY a.animal_id ORDER BY p.prod_date
    ROWS BETWEEN 6 PRECEDING AND CURRENT ROW) AS milk_7d_avg,
  RANK() OVER (PARTITION BY p.prod_date ORDER BY p.milk_liters DESC) AS rank_by_day
FROM production p
JOIN animals a ON a.animal_id = p.animal_id;

-- ---------- 6) SAMPLE lineage to demo recursive CTE ----------
-- If you don’t use mother_id/father_id, you can skip this.
UPDATE animals SET mother_id = NULL, father_id = NULL;
-- (Optional) Assign one simple lineage: animal 2’s mother is 1
UPDATE animals SET mother_id = 1 WHERE animal_id = 2;
