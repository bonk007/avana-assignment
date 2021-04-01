# AVANA Assignment (Test 2)
#### How to use
1. This source can be used for `.xls`, `.xlsx`, or `.ods` file.
2. Run `composer install` to install the dependencies.
3. Put your test file(s) into `spreadsheets` directory.
4. Run command `php src/console validate <filename>`

## Assignment Details
### Instruction
Write a psr-4 package to validate excel file format and its data. For this test, you will have to
validate two types of excel file Type_A and Type_B.

#### General Rules
1. Column name that starts with # should not contain any space
2. Column name that ends with * is a required column, means it must have a value
3. For each file type, it should validate the header columns name and the amount of
   columns it has.
   
   For example, Type_A file should only contains 5 columns and the header column name
   should be and follows the following order;
    - Field_A*
    - \#Field_B
    - Field_C
    - Field_D*
    - Field_E*
4. The package should be able to validate both .xls and .xlsx file
5. You may use third party library to parse the excel file.

#### Coding Recommendation
1. Follow DRY principle
2. Write simple but meaningful code
3. Incorporate design patterns in your code
