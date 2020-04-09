<?php

function covid19ImpactEstimator($data)
{
  $impact = getStats($data);

  $severeImpact = getStats($data, 'severe');

  return ['data' => $data, 'impact' => $impact, 'severeImpact' => $severeImpact];
}

function getStats($data, $case = 'regular')
{
  $multiplier = $case === 'severe' ? 50 : 10;

  $currentlyInfected = $data['reportedCases'] * $multiplier;

  $durationInDays = getDurationInDays($data['timeToElapse'], $data['periodType']);

  $infectionsByRequestedTime = $currentlyInfected * (2 ** floor($durationInDays / 3));

  $severeCasesByRequestedTime = 0.15 * $infectionsByRequestedTime;

  $hospitalBedsByRequestedTime =  (0.35 * $data['totalHospitalBeds']) - $severeCasesByRequestedTime;

  $casesForICUByRequestedTime = 0.05 * $infectionsByRequestedTime;

  $casesForVentilatorsByRequestedTime = 0.02 * $infectionsByRequestedTime;

  $region = $data['region'];

  $dollarsInFlight = $infectionsByRequestedTime * $durationInDays * $region['avgDailyIncomeInUSD'] * $region['avgDailyIncomePopulation'];


  return [
    'currentlyInfected' => (int) $currentlyInfected,
    'infectionsByRequestedTime' => (int) $infectionsByRequestedTime,
    'severeCasesByRequestedTime' => (int) $severeCasesByRequestedTime,
    'hospitalBedsByRequestedTime' => (int) $hospitalBedsByRequestedTime,
    'casesForICUByRequestedTime' => (int) $casesForICUByRequestedTime,
    'casesForVentilatorsByRequestedTime' => (int) $casesForVentilatorsByRequestedTime,
    'dollarsInFlight' => (int) $dollarsInFlight
  ];
}

function getDurationInDays($duration, $periodType)
{
  switch ($periodType) {
    case 'weeks':
      $computedDuration = $duration * 7;
      break;
    case 'months':
      $computedDuration = $duration * 30;
      break;
    default:
      $computedDuration = $duration;
  }
  return $computedDuration;
}
