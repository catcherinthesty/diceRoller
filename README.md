                      PHP Dice Roller

What is it?
-----------
This is a relatively simple php-based dice roller that accepts input in
standard dice notation and outputs a description of the outcome of the roll.

Installation
------------
Currently, it has very few requirements. Obviously, to use it as intended, you
must run it on a web server that has PHP with the random module compiled.
PHP must be compiled with pcre. Apart from that, requirements are negligible.

In future versions, however, mysqli or PDO may be required as well as the PHP
sessions module with appropriate webserver support.

I also have hopes to integrate this project into larget projects or as CMS
plugins, which may require changes to the standard I/O.

Usage
-----
An arbitrary number of dice statements and modifiers may be strung together
with "+" or "-"; a single division ("/") or multiplication ("*") operation is
also permitted, but MUST be the last operation and always modifies the full
sum of the roll. 
Dice may explode ("x"), in which case they are rolled again with the results
added to themselves, or may be rolled as "breeding" dice ("b") in which case an
additional die is rolled and added to the result. In non-dice-pool systems,
this is a cosmetic distinction.

Examples
--------
The following test cases work perfectly:
d		rolls a single d6
1b		rolls a single d6; roll an additional die on a result of 6
x6		rolls a single d6; roll it again and add the result on a 6
1d4+1		rolls a d4 and adds 1
1-1d4		subtract from 1 the roll of a d4
2d6+2d4*2	rolls two d6 and adds the result to 2d4; multiply by 2
4dF+2		rolls 4 FUDGE dice (-1,0,1) and adds 2
1d%		rolls a single 100 sided dice
4+d+2d8-d11/2	add to 4 the roll of a d6, 2d8, a d11 and divide the total by 2

Licensing
---------
This software is Licensed under the MIT License (MIT)

Copyright (c) 2013 Bill Parrott

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
