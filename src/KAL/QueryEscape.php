<?php

class KAL_QueryEscape {
    public function escapeString($string) {
        return mysql_escape_string($string);
    }

    public function escapeColumnName($string) {
        return '`'.str_replace('`', '``', $string).'`';
    }

    public function escapeMultilineComment($string) {
        // These can either terminate a comment, confuse the hell out of the parser,
        // make MySQL execute the comment as a query, or, in the case of semicolon,
        // are quasi-dangerous because the semicolon could turn a broken query into
        // a working query plus an ignored query.
        static $map = array(
            '--'  => '(DOUBLEDASH)',
            '*/'  => '(STARSLASH)',
            '//'  => '(SLASHSLASH)',
            '#'   => '(HASH)',
            '!'   => '(BANG)',
            ';'   => '(SEMICOLON)',
        );

        $comment = str_replace(
            array_keys($map),
            array_values($map),
            $comment);

        // For good measure, kill anything else that isn't a nice printable
        // character.
        $comment = preg_replace('/[^\x20-\x7F]+/', ' ', $comment);
        return '/* '.$comment.' */';
    }

    public function escapeStringForLikeClause($string) {
        $value = addcslashes($value, '\%_');
        $value = $this->escapeString($value);
        return $value;
    }
}

