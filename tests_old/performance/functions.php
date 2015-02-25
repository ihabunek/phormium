<?php

/** Start timer */
function start($name)
{
    global $start, $id;
    $id = $name;
    echo str_pad($name, 20);
    $start = microtime(true);
}

/** End timer */
function finish()
{
    global $start, $stats, $id;
    $duration = round(microtime(true) - $start, 4);
    $stats[$id] = $duration;

    $duration = str_pad($duration, 6, " ", STR_PAD_LEFT);
    echo "[$duration]\n";
}

/** Save stats to disk */
function save()
{
    global $stats;

    $branch = git_branch();
    $hash = git_hash();
    $dir = __DIR__;

    $base = "$dir/results/$branch-$hash";
    file_put_contents("$base.json", json_encode($stats, JSON_PRETTY_PRINT));
}

/** Calls $callback $num times.*/
function repeat($num, $callback) {
    for ($i=0; $i < $num; $i++) {
        $callback();
    }
}

/** Returns current git branch. */
function git_branch()
{
    $out = rtrim(`git branch`);
    $lines = explode("\n", $out);
    foreach($lines as $line) {
        if ($line[0] == "*") {
            return trim(substr($line, 2));
        }
    }
}

/** Returns the short has of the last commit. */
function git_hash()
{
    return `git log -n 1 --pretty=format:%h`;
}
