/** 
*
* Vesta Web Interface
*
* Copyright (C) 2019 Carter Roeser <carter@cdgtech.one>
* https://cdgco.github.io/VestaWebInterface
*
* Vesta Web Interface is free software: you can redistribute it and/or modify
* it under the terms of version 3 of the GNU General Public License as published 
* by the Free Software Foundation.
*
* Vesta Web Interface is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with Vesta Web Interface.  If not, see
* <https://github.com/cdgco/VestaWebInterface/blob/master/LICENSE>.
*
*/

/*
* Table structure for table `vwi_billing-config`
*/

CREATE TABLE IF NOT EXISTS `vwi_billing-config` (
  `VARIABLE` varchar(128) CHARACTER SET utf8 NOT NULL,
  `VALUE` varchar(128) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `vwi_billing-config` (`VARIABLE`, `VALUE`) VALUES
('pub_key', ''),
('sec_key', '');

ALTER TABLE `vwi_billing-config`
  ADD PRIMARY KEY (`VARIABLE`);

/*
* Table structure for table `vwi_billing-plans`
*/

CREATE TABLE IF NOT EXISTS `vwi_billing-plans` (
  `PACKAGE` varchar(128) CHARACTER SET utf8 NOT NULL,
  `ID` varchar(128) CHARACTER SET utf8 NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

ALTER TABLE `vwi_billing-plans`
  ADD PRIMARY KEY (`PACKAGE`);