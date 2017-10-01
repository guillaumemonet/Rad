<?php

/*
 * The MIT License
 *
 * Copyright 2017 guillaume.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Rad\Database;

use PDO;
use PDOStatement;

/**
 * Description of DatabaseInterface
 *
 * @author guillaume
 */
interface DatabaseInterface {

    public function connect(string $login, string $password, string $host, int $port, string $database = null): DatabaseInterface;

    public function change(string $dbname): DatabaseInterface;

    public function prepare(string $sql): PDOStatement;

    public function query(string $sql): PDOStatement;

    public function fetch(PDOStatement $stmt, int $mode = PDO::FETCH_ASSOC): PDOStatement;

    public function fetchAssoc(PDOStatement $stmt): PDOStatement;

    public function fetchArray(PDOStatement $stmt): PDOStatement;

    public function fetchAll(PDOStatement $stmt, int $mode = PDO::FETCH_ASSOC): PDOStatement;

    public function execute(PDOStatement $stmt, $input_parameters = null): PDOStatement;

    public function ping(): bool;

    public function schema(string $table): PDOStatement;

    public function lastInsertId(): int;
}
