<?php

/*API NO.7*/
function selectMovie(){
    $pdo = pdoSqlConnect();

        $query = "SELECT a.id, a.title, a.mainImg
                    FROM movies AS a
                    LEFT OUTER JOIN (
                                     SELECT a.id AS movieId,TRUNCATE(c.ticketingCount/c.totalCount*100,1) AS ticketingRatio
                                       FROM movies AS a
                                       JOIN (
                                             SELECT movieId, count(*) AS ticketingCount,(SELECT count(*) FROM ticketing) AS totalCount FROM ticketing GROUP BY movieId) AS c
                                         ON a.id = c.movieId
                                            ) AS c
                                 ON a.id = c.movieId
                   WHERE movieStatus = 1
                   ORDER BY c.ticketingRatio DESC";

    $st = $pdo->prepare($query);
    $st->execute();
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res;
}

/*API NO.8*/
function checkTheater($movieId, $date){
    $pdo = pdoSqlConnect();

    if(!$date){
        $query = "SELECT title, cm.date, viewAge, runningTime, mainImg
                    FROM movies
                    LEFT JOIN current_movies cm on movieId = movies.id
                   GROUP BY title, cm.date, viewAge, runningTime, mainImg, movies.movieStatus, movies.id
                  HAVING movies.movieStatus = 1 and movies.id = ? and cm.date = CURDATE()";

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $query = "SELECT theater.id AS theaterRoomId, theater.theaterId,theater.theaterName, theater.floor, cm.room
                    FROM theater
                    LEFT JOIN current_movies cm on theater.theaterId = cm.theaterId AND cm.room = theater.roomId
                   GROUP BY theater.id, theater.theaterId, theater.theaterName, theater.floor, cm.room, cm.movieId, cm.date
                  HAVING cm.movieId = ? and cm.date = CURDATE()";

         $st = $pdo->prepare($query);
         $st->execute([$movieId]);
         $st->setFetchMode(PDO::FETCH_ASSOC);
         $theater = $st->fetchAll();

         $res[0]["theaters"] = $theater;
        /*
        $query = "SELECT t.id, cm.startTime, cm.endTime, t.totalSeat
                    FROM current_movies cm
                    LEFT JOIN theater t ON cm.theaterId = t.theaterId AND cm.room = t.roomId
                   GROUP BY t.id, cm.startTime, cm.endTime, t.totalSeat, cm.date, cm.movieId
                  HAVING cm.movieId = 1 AND cm.date = '2020-02-23'
                   ORDER BY id";

        $st = $pdo->prepare($query);
        $st->execute([$movieId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $timeTable = $st->fetchAll();

        $res[0]["theaters"]["timeTable"] = $timeTable;
*/
    }
    else {
        $query = "SELECT title, cm.date, viewAge, runningTime, mainImg
                    FROM movies
                    LEFT JOIN current_movies cm on movieId = movies.id
                   GROUP BY title, cm.date, viewAge, runningTime, mainImg, movies.movieStatus, movies.id
                  HAVING movies.movieStatus = 1 and movies.id = ? and cm.date = ?";

        $st = $pdo->prepare($query);
        $st->execute([$movieId, $date]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $query = "SELECT theater.id AS theaterRoomId, theater.theaterId,theater.theaterName, theater.floor, cm.room
                    FROM theater
                    LEFT JOIN current_movies cm on theater.theaterId = cm.theaterId AND cm.room = theater.roomId
                   GROUP BY theater.id, theater.theaterId, theater.theaterName, theater.floor, cm.room, cm.movieId, cm.date
                  HAVING cm.movieId = ? and cm.date = ?";

        $st = $pdo->prepare($query);
        $st->execute([$movieId, $date]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $theater = $st->fetchAll();

        $res[0]["theaters"] = $theater;
    }
    $st = null;
    $pdo = null;

    return $res;
}

function checkBookMovie($movieId, $theaterId, $date){
    $pdo = pdoSqlConnect();
    if(!$date){
        $query = "SELECT theater.theaterId,theater.theaterName, cm.date
                    FROM theater
                    LEFT JOIN current_movies cm on theater.theaterId = cm.theaterId
                    GROUP BY theater.theaterId, theater.theaterName, theater.floor, cm.room, cm.movieId, cm.date
                  HAVING cm.movieId = ? and cm.date = CURDATE() and theaterId = ? LIMIT 1";

        $st = $pdo->prepare($query);
        $st->execute([$movieId, $theaterId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $query = "SELECT cm.id AS uniqueMovieTImeId, t.id as uniqueRoomId, cm.startTime, cm.endTime, cm.seatCount, t.totalSeat
                    FROM current_movies cm
                    LEFT JOIN theater t ON cm.theaterId = t.theaterId AND cm.room = t.roomId
                   GROUP BY t.id, cm.startTime, cm.endTime, t.totalSeat, cm.date, cm.movieId, cm.seatCount, t.theaterId, cm.id
                  HAVING cm.movieId = ? AND cm.date = CURDATE() AND t.theaterId = ?
                   ORDER BY t.id";

        $st = $pdo->prepare($query);
        $st->execute([$movieId, $theaterId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $theater = $st->fetchAll();

        $res[0]["time"] = $theater;
    }
    else {
        $query = "SELECT theater.theaterId,theater.theaterName, cm.date
                    FROM theater
                    LEFT JOIN current_movies cm on theater.theaterId = cm.theaterId
                    GROUP BY theater.theaterId, theater.theaterName, theater.floor, cm.room, cm.movieId, cm.date
                  HAVING cm.movieId = ? and cm.date = ? and theaterId = ? LIMIT 1";

        $st = $pdo->prepare($query);
        $st->execute([$movieId, $date, $theaterId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $res = $st->fetchAll();

        $query = "SELECT cm.id AS uniqueMovieTImeId, t.id as uniqueRoomId, cm.startTime, cm.endTime, cm.seatCount, t.totalSeat
                    FROM current_movies cm
                    LEFT JOIN theater t ON cm.theaterId = t.theaterId AND cm.room = t.roomId
                   GROUP BY t.id, cm.startTime, cm.endTime, t.totalSeat, cm.date, cm.movieId, cm.seatCount, t.theaterId, cm.id
                  HAVING cm.movieId = ? AND cm.date = ? AND t.theaterId = ?
                   ORDER BY t.id";

        $st = $pdo->prepare($query);
        $st->execute([$movieId, $date, $theaterId]);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $theater = $st->fetchAll();

        $res[0]["time"] = $theater;
    }


    $st = null;
    $pdo = null;

    return $res[0];
}

function ticketInfo($movieTimeId){
    $pdo = pdoSqlConnect();

    $query = "SELECT cm.id, t.theaterName, t.roomId, t.floor, t.totalSeat, cm.seatCount,
                       cm.startTime, cm.endTime, cm.seatCount, t.totalSeat, t.description,
                       date_format(cm.date, '%Y.%m.%d') AS date,
                       CASE DAYOFWEEK(cm.date)
                       WHEN '1' THEN '일'
                       WHEN '2' THEN '월'
                       WHEN '3' THEN '화'
                       WHEN '4' THEN '수'
                       WHEN '5' THEN '목'
                       WHEN '6' THEN '금'
                       WHEN '7' THEN '토'
                       END AS week
                  FROM current_movies cm
                  LEFT JOIN theater t ON cm.theaterId = t.theaterId AND cm.room = t.roomId
                 GROUP BY cm.startTime, cm.endTime, t.totalSeat, cm.date, cm.movieId, cm.seatCount, t.theaterId, cm.id, t.theaterName, t.floor, t.roomId, t.totalSeat, cm.seatCount, t.description
                HAVING cm.id = ?";

    $st = $pdo->prepare($query);
    $st->execute([$movieTimeId]);
    $st->setFetchMode(PDO::FETCH_ASSOC);
    $res = $st->fetchAll();

    $st = null;
    $pdo = null;

    return $res[0];
}

