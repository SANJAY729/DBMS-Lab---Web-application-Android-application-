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
