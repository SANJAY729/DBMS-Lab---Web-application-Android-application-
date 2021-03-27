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

//INSERT STUFF TO BE DONE