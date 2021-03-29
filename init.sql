CREATE TABLE IF NOT EXISTS login_details (
    uname VARCHAR(50) NOT NULL PRIMARY KEY,
    pwd VARCHAR(50) NOT NULL,
    utype VARCHAR(50) NOT NULL
);

CREATE TABLE IF NOT EXISTS customers (
    c_uname VARCHAR(50) NOT NULL PRIMARY KEY,
    c_name VARCHAR(50) NOT NULL,
    c_address VARCHAR(120) NOT NULL,
    c_phno VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS restaurants (
    r_uname VARCHAR(50) NOT NULL PRIMARY KEY,
    r_name VARCHAR(50) NOT NULL,
    r_address VARCHAR(120) NOT NULL,
    r_phno VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS delivery_agents (
    da_uname VARCHAR(50) NOT NULL PRIMARY KEY,
    da_name VARCHAR(50) NOT NULL,
    da_address VARCHAR(120) NOT NULL,
    da_phno VARCHAR(10) NOT NULL
);

CREATE TABLE IF NOT EXISTS products (
    p_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    r_uname VARCHAR(50),
    p_name VARCHAR(50) NOT NULL,
    p_cost INT NOT NULL,
    p_time INT NOT NULL,
    p_description VARCHAR(200) NOT NULL,
    FOREIGN KEY (r_uname) REFERENCES restaurants(r_uname)
);

ALTER TABLE products AUTO_INCREMENT=100;

CREATE TABLE IF NOT EXISTS orders (
    o_id INT NOT NULL PRIMARY KEY,
    r_uname VARCHAR(50) NOT NULL,
    total_cost INT NOT NULL,
    expected_time INT NOT NULL,
    order_status VARCHAR(50) DEFAULT "Preparing",
    FOREIGN KEY (r_uname) REFERENCES restaurants(r_uname)
);

CREATE TABLE IF NOT EXISTS assigned (
    o_id INT NOT NULL PRIMARY KEY,
    da_uname VARCHAR(50) NOT NULL,
    delivery_time INT NOT NULL DEFAULT 15,
    FOREIGN KEY (o_id) REFERENCES orders(o_id)
);

CREATE TABLE IF NOT EXISTS places (
    o_id INT NOT NULL PRIMARY KEY,
    c_uname VARCHAR(50) NOT NULL,
    total_time INT NOT NULL,
    FOREIGN KEY (o_id) REFERENCES orders(o_id),
    FOREIGN KEY (c_uname) REFERENCES customers(c_uname)
);

CREATE TABLE IF NOT EXISTS contains (
    o_id INT NOT NULL,
    p_id INT NOT NULL,
    quantity INT NOT NULL,
    FOREIGN KEY (p_id) REFERENCES products(p_id),
    PRIMARY KEY (o_id, p_id)
);

INSERT INTO login_details VALUES("c1","c1","Customer");
INSERT INTO login_details VALUES("c2","c2","Customer");
INSERT INTO login_details VALUES("r1","r1","Restaurant");
INSERT INTO login_details VALUES("r2","r2","Restaurant");
INSERT INTO login_details VALUES("d1","d1","Delivery Agent");
INSERT INTO login_details VALUES("d2","d2","Delivery Agent");
INSERT INTO login_details VALUES("d3","d3","Delivery Agent");

INSERT INTO customers VALUES("c1","cus1","lbs",11111);
INSERT INTO customers VALUES("c2","cus2","azad",22222);
INSERT INTO restaurants VALUES("r1","res1","pan loop",33333);
INSERT INTO restaurants VALUES("r2","res2","nalanda",44444);
INSERT INTO delivery_agents VALUES("d1","dag1","rp",55555);
INSERT INTO delivery_agents VALUES("d2","dag2","rk",66241);
INSERT INTO delivery_agents VALUES("d3","dag3","ms",76891);

INSERT INTO products(r_uname,p_name,p_cost,p_time,p_description) VALUES("r1","burger",50,10,"veg");
INSERT INTO products(r_uname,p_name,p_cost,p_time,p_description) VALUES("r1","fries",30,5,"snacks");
INSERT INTO products(r_uname,p_name,p_cost,p_time,p_description) VALUES("r2","dosa",30,10,"breakfast");
INSERT INTO products(r_uname,p_name,p_cost,p_time,p_description) VALUES("r2","idli(4pc)",30,10,"breakfast");
INSERT INTO products(r_uname,p_name,p_cost,p_time,p_description) VALUES("r1","coke",10,8,"drink");
