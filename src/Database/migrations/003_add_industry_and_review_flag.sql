ALTER TABLE customers ADD COLUMN industry VARCHAR(100);
ALTER TABLE customers ADD COLUMN needs_review BOOLEAN DEFAULT FALSE;
ALTER TABLE customers ADD COLUMN review_reason VARCHAR(255);
CREATE INDEX idx_customers_industry ON customers(industry);
CREATE INDEX idx_customers_needs_review ON customers(needs_review);
